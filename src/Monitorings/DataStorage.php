<?php

namespace Arrilot\BitrixSystemCheck\Monitorings;

use Bitrix\Main\Application;

class DataStorage
{
    /**
     * @var \Bitrix\Main\DB\Connection
     */
    protected $connection;

    /**
     * @var \Bitrix\Main\Db\SqlHelper
     */
    protected $helper;

    /**
     * @var string
     */
    protected $monitoringName;

    /**
     * DataStorage constructor.
     * @param $monitoringName
     */
    public function __construct($monitoringName)
    {
        $this->connection = Application::getConnection();
        $this->helper = $this->connection->getSqlHelper();
        $this->monitoringName = $monitoringName;
    }

    /**
     * @param $check
     * @return false|array
     */
    public function getData($check)
    {
        $sql = sprintf(
            "SELECT * FROM arrilot_systemcheck_checks_data WHERE `MONITORING`='%s' AND `CHECK`='%s' ORDER BY `CREATED_AT` DESC LIMIT 1",
            $this->helper->forSql($this->monitoringName),
            $this->helper->forSql($check)
        );

        return $this->connection->query($sql)->fetch();
    }
    
    public function saveData($check, $data)
    {
        $sql = sprintf("INSERT INTO arrilot_systemcheck_checks_data (`MONITORING`, `CHECK`, `DATA`, `CREATED_AT`) VALUES ('%s', '%s', '%s', NOW())",
            $this->helper->forSql($this->monitoringName),
            $this->helper->forSql($check),
            $this->helper->forSql(json_encode($data))
        );

        $this->connection->query($sql);

        return $this;
    }
    
    /**
     * Delete all outdated data for this monitoring.
     *
     * @param int $days
     * @return DataStorage
     */
    public function cleanOutdatedData($days)
    {
        $sql = sprintf(
            "DELETE FROM arrilot_systemcheck_checks_data WHERE `MONITORING`='%s' AND `CREATED_AT` < NOW() - INTERVAL %s DAY",
            $this->helper->forSql($this->monitoringName),
            (int) $days
        );
        
        //dd($sql);
    
        $this->connection->query($sql);
        
        return $this;
    }
}
