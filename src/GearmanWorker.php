<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2018  Daniel Ziegler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Statusengine;

use Statusengine\Config\WorkerConfig;
use Statusengine\QueueingEngines\QueueInterface;

class GearmanWorker implements QueueInterface {
    /**
     * @var WorkerConfig
     */
    private $WorkerConfig;

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var \GearmanWorker
     */
    private $worker;

    /**
     * @var mixed
     */
    private $lastJobData;

    /**
     * @var array
     */
    private $queues = [];

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * GearmanWorker constructor.
     * @param WorkerConfig $WorkerConfig
     * @param Config $Config
     * @param Syslog $Syslog
     */
    public function __construct(WorkerConfig $WorkerConfig, Config $Config, Syslog $Syslog) {
        $this->WorkerConfig = $WorkerConfig;
        $this->Config = $Config;
        $this->Syslog = $Syslog;
        $this->addQueue($this->WorkerConfig);
    }

    public function addQueue(WorkerConfig $WorkerConfig) {
        $this->queues[] = $WorkerConfig->getQueueName();
    }

    public function connect() {
        $config = $this->Config->getGearmanConfig();

        $this->worker = new \GearmanWorker();
        $this->worker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
        $this->worker->addServer($config['address'], $config['port']);
        $this->worker->setTimeout($config['timeout']);
        foreach ($this->queues as $queue) {
            $this->worker->addFunction($queue, [$this, 'handleJob']);
        }
    }

    public function disconnect() {
        unset($this->worker);
    }

    /**
     * @return \stdObject|null
     */
    public function getJob() {
        $this->worker->work();

        @$this->worker->wait();

        $jobData = $this->lastJobData;
        $this->lastJobData = null;
        return $jobData;
    }

    /**
     * @param \GearmanJob $job
     * @return void
     */
    public function handleJob($job) {
        $this->lastJobData = null;

        $data = JSONUTF8::decodeJson($job->workload(), $this->Syslog);
        if($data){
            $this->lastJobData = $data;
        }
    }

}