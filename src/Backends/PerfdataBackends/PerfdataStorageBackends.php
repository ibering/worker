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

namespace Statusengine\Backends\PerfdataBackends;


use Statusengine\BulkInsertObjectStore;
use Statusengine\Config;
use Statusengine\Crate\Crate;
use Statusengine\Mysql\MySQL;
use Statusengine\Syslog;

class PerfdataStorageBackends {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var BulkInsertObjectStore
     */
    private $MySQLBulkInsertObjectStore;

    /**
     * @var BulkInsertObjectStore
     */
    private $CrateBulkInsertObjectStore;

    public function __construct(Config $Config, BulkInsertObjectStore $BulkInsertObjectStore, Syslog $Syslog) {
        $this->Config = $Config;

        // todo remove clone, don't know how smart this is...
        $this->MySQLBulkInsertObjectStore = clone $BulkInsertObjectStore;
        $this->CrateBulkInsertObjectStore = clone $BulkInsertObjectStore;
        $this->Syslog = $Syslog;
    }

    /**
     * @return array with BackendObjects
     */
    public function getBackends() {
        $backends = [];

        if ($this->Config->isCratePerfdataBackend()) {
            $backends['crate'] = new Crate($this->Config, $this->CrateBulkInsertObjectStore, $this->Syslog);
        }

        if ($this->Config->isGraphitePerfdataBackend()) {
            $backends['graphite'] = new GraphitePerfdata($this->Config, $this->Syslog);
        }

        if ($this->Config->isMysqlPerfdataBackend()) {
            $backends['mysql'] = new MySQL($this->Config, $this->MySQLBulkInsertObjectStore, $this->Syslog);
        }

        if ($this->Config->isElasticsearchPerfdataBackend()) {
            $backends['elasticsearch'] = new ElasticsearchPerfdata($this->Config, $this->Syslog);
        }

        return $backends;
    }

}
