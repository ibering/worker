<?php
/**
 * Statusengine UI
 * Copyright (C) 2018  Daniel Ziegler
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

namespace Statusengine\QueueingEngines;


use Statusengine\Config;
use Statusengine\GearmanWorker;

class QueueingEngine {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Config\WorkerConfig
     */
    private $WorkerConfig;

    public function __construct(Config $Config, Config\WorkerConfig $WorkerConfig) {
        $this->Config = $Config;
        $this->WorkerConfig = $WorkerConfig;
    }

    /**
     * @return QueueInterface
     */
    public function getQueue(){
        if($this->Config->isGearmanEnabled()){
            return new GearmanWorker($this->WorkerConfig, $this->Config);
        }

        if($this->Config->isRabbitMqEnabled()){

        }
    }

}
