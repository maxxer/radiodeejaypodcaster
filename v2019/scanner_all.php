<?php

/**
 * Radio Deejay RELOADED podcaster
 * @author Lorenzo Milesi <lorenzo@mile.si>
 * @copyright 2016 Lorenzo Milesi
 * @license GNU GPL v3
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

if (empty(trim($argv[1])))
    exit(1);
require_once 'rdjreloaded.php';

$lib = new RDJReloaded();
$lib->aggiornaPodcast(trim($argv[1]), true);
