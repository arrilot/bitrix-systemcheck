<?php

namespace Arrilot\BitrixSystemCheck\Console;

use Arrilot\BitrixSystemCheck\Checks\Check;
use Arrilot\BitrixSystemCheck\CommonChecksRepository;
use Arrilot\BitrixSystemCheck\Exceptions\FailCheckException;
use Arrilot\BitrixSystemCheck\Exceptions\SkipCheckException;
use Bitrix\Main\Config\Configuration;
use Exception;
use Symfony\Component\Console\Command\Command;
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
     * Bitrix config for this package.
     *
     * @var array
     */
    protected $config;
    
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('system:check')
            ->setDescription('Check system configuration issues');
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
        $this->config = Configuration::getInstance()->get('bitrix-systemcheck');
        $env = !empty($this->config['env']) ? $this->config['env'] : 'local';
        $this->runChecks($this->getCommonChecks(), 'Запуск общих проверок');
        $this->runChecks($this->getCustomChecks(), 'Запуск проверок для окружения ('. $env . ')');
    
        if (count($this->skips) && $this->output->isVerbose()) {
            $this->output->writeln('<fg=yellow>Журнал пропуска проверок:</fg=yellow>');
            $this->output->writeln('');
            foreach ($this->skips as $message) {
                $this->output->writeln('<fg=yellow>'.$message.'</fg=yellow>');
            }
        }
    
        if (count($this->errors)) {
            $this->error('Журнал ошибок:');
            $this->output->writeln('');
            foreach ($this->errors as $message) {
                $this->output->writeln('<fg=red>'.$message.'</fg=red>');
            }
            return 1;
        }
        $this->info('Все проверки успешно пройдены.');
        return 0;
    }
    
    protected function runChecks(array $checks, string $title)
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
            $this->output->write(sprintf('<fg=yellow>Проверка %s/%s:</fg=yellow> %s ', $current, $max, $check->getName()));
            $this->runCheck($check);
            $current++;
        }
        $this->output->writeln('');
    }
    
    private function getCommonChecks()
    {
        $repository = new CommonChecksRepository();
        $checksNames = array_filter($repository->getChecks(), function($checkName) {
            return !in_array($checkName, $this->config['skipCommonChecks']);
        });

        return array_map(function($checkName) {
            return new $checkName($this->config);
        }, $checksNames);
    }
    
    private function getCustomChecks()
    {
        return [];
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
    }
}