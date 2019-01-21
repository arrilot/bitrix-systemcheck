<?php

namespace Arrilot\BitrixSystemCheck\Console;

use Arrilot\BitrixSystemCheck\Checks\Check;
use Arrilot\BitrixSystemCheck\CommonChecksRepository;
use Arrilot\BitrixSystemCheck\Exceptions\FailCheckException;
use Arrilot\BitrixSystemCheck\Exceptions\SkipCheckException;
use Arrilot\BitrixSystemCheck\Monitorings\Monitoring;
use Bitrix\Main\Config\Configuration;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\LoggerInterfaceTest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SystemCheckCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;
    
    /**
     * @var OutputInterface
     */
    protected $output;
    
    /**
     * Array of error messages.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Array of skip messages.
     *
     * @var array
     */
    protected $skips = [];
    
    /**
     * Monitoring object.
     *
     * @var LoggerInterface|null
     */
    protected $logger;
    
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('system:check')
            ->setDescription('Run a specific system monitoring')
            ->addArgument('monitoring', InputArgument::REQUIRED, 'Monitoring name');
    }
    
    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $isVerbose = $this->output->isVerbose();
        
        $config = Configuration::getInstance()->get('bitrix-systemcheck');
        $monitoringName = $input->getArgument('monitoring');
        if (!isset($config['monitorings'][$monitoringName])) {
            $this->output->writeln('<fg=red>Мониторинг '.$monitoringName.' не найден</fg=red>');
            return 1;
        }
        /** @var Monitoring $monitoring */
        $monitoring = new $config['monitorings'][$monitoringName];
        $this->logger = $monitoring->logger();
        $title = !empty($config['env'])
            ? 'Запуск проверок мониторинга '.$monitoringName.' для окружения '. $config['env'] . ''
            : 'Запуск проверок мониторинга '.$monitoringName.'';

        $monitoring->getDataStorage()->cleanOutdatedData($monitoring->dataTtlDays);
        $this->runChecks($monitoring->checks(), $monitoring, $title, $isVerbose);
    
        if (count($this->skips) && $isVerbose) {
            $this->output->writeln('<fg=yellow>Журнал пропуска проверок:</fg=yellow>');
            $this->output->writeln('');
            foreach ($this->skips as $message) {
                $this->output->writeln('<fg=yellow>'.$message.'</fg=yellow>');
            }
        }
    
        if (count($this->errors)) {
            foreach ($this->errors as $message) {
                $this->output->writeln('<fg=red>'.$message.'</fg=red>');
            }
    
            $this->output->writeln('');
    
            $this->output->writeln('<error>Некоторые проверки завершились ошибками</error>');
    
            $this->raiseAlert();
            return 1;
        }

        $this->info('Все проверки успешно пройдены');
        return 0;
    }
    
    protected function runChecks(array $checks, Monitoring $monitoring, $title, $isVerbose)
    {
        $max = count($checks);
        if ($max === 0) {
            return;
        }
        $current = 1;
        $this->output->writeln('|-------------------------------------');
        $this->output->writeln('| '.$title);
        $this->output->writeln('|-------------------------------------');
        foreach ($checks as $check) {
            $check->setDataStorage($monitoring->getDataStorage());
            $message = sprintf(
                '<fg=yellow>Проверка %s/%s:</fg=yellow>%s %s ',
                $current,
                $max,
                $isVerbose ? ' '. get_class($check) : '',
                $check->getName()
            );
            $this->output->write($message);
            $this->runCheck($check);
            $current++;
        }
        $this->output->writeln('');
    }

    protected function runCheck(Check $check)
    {
        try {
            if ($check->run()) {
                $this->output->write('<fg=green>✔</fg=green>');
            } else {
                $this->output->write('<fg=red>✘</fg=red>');
                foreach ($check->getMessages() as $errorMessage) {
                    $this->errors[] = $errorMessage;
                }
            }
        } catch (SkipCheckException $e) {
            $this->output->write('<fg=yellow>-</fg=yellow>');
            $this->skips[] = $e->getMessage();
        } catch (FailCheckException $e) {
            $this->output->write('<fg=red>✘</fg=red>');
            $this->errors[] = $e->getMessage();
        } catch (Exception $e) {
            $this->output->write('<fg=red>✘</fg=red>');
            $this->errors[] = $e->getMessage();
        }
     
        $this->output->write(PHP_EOL);
    }
    
    /**
     * Echo an error message.
     *
     * @param string$message
     */
    protected function error($message)
    {
        $this->output->writeln("<error>{$message}</error>");
    }
    
    /**
     * Echo an info.
     *
     * @param string $message
     */
    protected function info($message)
    {
        $this->output->writeln("<info>{$message}</info>");
        if ($this->logger) {
            $this->logger->info($message);
        }
    }
    
    protected function raiseAlert()
    {
        if ($this->logger) {
            $message = implode(PHP_EOL, $this->errors);
            $this->logger->alert($message);
        }
    }
}