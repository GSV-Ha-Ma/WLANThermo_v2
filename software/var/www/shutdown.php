<?php
/*************************************************** 
    Copyright (C) 2018  Stephan Marrtin
    ***************************
		@author Stephan Martin
		@version 0.1, 06/04/18
	***************************
	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
    HISTORY: Please refer Github History
****************************************************/

	include("./function.php");
	$inipath = './conf/WLANThermo.conf';
	
	if(get_magic_quotes_runtime()) set_magic_quotes_runtime(0); 
	$ini = getConfig("./conf/WLANThermo.conf", ";");  // dabei ist ; das zeichen für einen kommentar. kann geändert werden.
	$ini['ToDo']['raspi_shutdown'] = "True";
	write_ini($inipath, $ini);
	
	echo(true);

?>