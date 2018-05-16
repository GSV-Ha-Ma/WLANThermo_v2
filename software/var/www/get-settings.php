<?php
 /*************************************************** 
    Copyright (C) 2018  Stephan Martin
    ***************************
		@author Stephan Martin
		@version 0.1, 04/04/18
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
session_start();
include("function.php");
//-----------------------------------------------------------------------------
// WLANThermo config path
$thermoConfigFile = './conf/WLANThermo.conf'; 
session($thermoConfigFile);
//-----------------------------------------------------------------------------
//Time test:
/*
$before = microtime(true); //designer2k2 hack!
for ($i=0 ; $i<100 ; $i++) {
	getSettings();
}	
$after = microtime(true);
echo ($after-$before)/$i . " sec/perRun\n";

/*
0.141sec total
0.058 for getSSID
0.096 for getCPUID => 0.000008 now with session
*/
//-----------------------------------------------------------------------------

header('Content-type:application/json;charset=utf-8');	
echo json_encode(getSettings(), JSON_UNESCAPED_SLASHES);

function getSettings(){
	
	$thermoConfigFile = './conf/WLANThermo.conf'; 
	$thermoConfig = getConfig($thermoConfigFile, ";");
	$output = array();
	
	
	//system block:
	$output['system']['time'] = time();
	$output['system']['ap'] = getSSID(); 
	$output['system']['host'] = getCPUID();
	$output['system']['language'] = substr($_SESSION["locale"],0,2);
	$output['system']['unit'] = strtoupper(substr($_SESSION["temp_unit"],0,1));
	$output['system']['locale'] = $thermoConfig['locale']['locale'];
	$output['system']['version'] = isset($_SESSION["webGUIversion"]) ? $_SESSION["webGUIversion"]: 'V2.7.0';	//$_SESSION["webGUIversion"] is only set in header.php
	$output['system']['getupdate'] = filter_var($_SESSION["updateAvailable"],FILTER_VALIDATE_BOOLEAN);
	$output['system']['autoupd'] = filter_var($thermoConfig['update']['update_enabled'],FILTER_VALIDATE_BOOLEAN);
	$output['system']['hwversion'] = $thermoConfig["Hardware"]["version"];
	$output['system']['max31855'] = filter_var($thermoConfig['Hardware']['max31855'],FILTER_VALIDATE_BOOLEAN);
	$output['system']['max31855'] = filter_var($thermoConfig['Hardware']['max31855'],FILTER_VALIDATE_BOOLEAN);
	$output['system']['maverick_enabled'] = filter_var($thermoConfig['ToDo']['maverick_enabled'],FILTER_VALIDATE_BOOLEAN);
	$output['system']['beeper_enabled'] = filter_var($thermoConfig['Sound']['beeper_enabled'],FILTER_VALIDATE_BOOLEAN);
	$output['system']['beeper_on_start'] = filter_var($thermoConfig['Sound']['beeper_on_start'],FILTER_VALIDATE_BOOLEAN);
	
	//sensors: 
	$sensorConfigFile = './conf/sensor.conf'; 
	$sensorConfig = getConfig($sensorConfigFile, ";");
	$sensors = array();
	foreach($sensorConfig AS $sensor_number => $sensor_name)
	{
		 array_push($sensors, $sensor_name['name']);
	}
	$output['sensors'] = $sensors;
	
	//profil:
	$helper = array('Pitmaster');
	if ($thermoConfig["Hardware"]["version"] == 'miniV2'){
		array_push($helper,'Pitmaster2');
	}
	
	foreach ($helper as $key => $value){
		$profil = array();
		$profil['id'] = $key;
		$profil['name'] = 'Profil ' . strval($key+1);
		$profil['aktor'] = $thermoConfig[$value]['pit_type'];
		$profil['pause'] = $thermoConfig[$value]['pit_pause'];
		$profil['DCmin'] = $thermoConfig[$value]['pit_pwm_min'];
		$profil['DCmax'] = $thermoConfig[$value]['pit_pwm_max'];
		$profil['DCinv'] = filter_var($thermoConfig[$value]['pit_inverted'],FILTER_VALIDATE_BOOLEAN);
		$profil['SPmin'] = $thermoConfig[$value]['pit_servo_min'];
		$profil['SPmax'] = $thermoConfig[$value]['pit_servo_max'];
		$profil['SPinv'] = filter_var($thermoConfig[$value]['pit_servo_inverted'],FILTER_VALIDATE_BOOLEAN);

		$profil['damper_offset'] = $thermoConfig[$value]['pit_damper_offset'];
		$profil['damper_pitch'] = $thermoConfig[$value]['pit_damper_pitch'];
		$profil['ratelimit_rise'] = $thermoConfig[$value]['pit_ratelimit_rise'];
		$profil['ratelimit_fall'] = $thermoConfig[$value]['pit_ratelimit_fall'];
		$profil['servo_deadband'] = $thermoConfig[$value]['pit_servo_deadband'];	
		
		$profil['curve'] = $thermoConfig[$value]['pit_curve'];	
		
		$profil['controller'] = $thermoConfig[$value]['pit_controller_type']=="PID" ? true : false;
		$profil['Kp'] = $thermoConfig[$value]['pit_kp'];
		$profil['Ki'] = $thermoConfig[$value]['pit_ki'];
		$profil['Kd'] = $thermoConfig[$value]['pit_kd'];
		$profil['Kp_a'] = $thermoConfig[$value]['pit_kp_a'];
		$profil['Ki_a'] = $thermoConfig[$value]['pit_ki_a'];	
		$profil['Kd_a'] = $thermoConfig[$value]['pit_kd_a'];	
		$profil['i_min'] = $thermoConfig[$value]['pit_iterm_min'];
		$profil['i_max'] = $thermoConfig[$value]['pit_iterm_max'];	
		$profil['switch'] = $thermoConfig[$value]['pit_switch_a'];

		$profil['startup_min'] = $thermoConfig[$value]['pit_startup_min'];
		$profil['startup_th'] = $thermoConfig[$value]['pit_startup_threshold'];	
		$profil['startup_time'] = $thermoConfig[$value]['pit_startup_time'];
		
		$profil['OLon'] = filter_var($thermoConfig[$value]['pit_open_lid_detection'],FILTER_VALIDATE_BOOLEAN);
		
		$profil['OLpause'] = $thermoConfig[$value]['pit_open_lid_pause'];
		$profil['OLfall'] = $thermoConfig[$value]['pit_open_lid_falling_border'];	
		$profil['OLrise'] = $thermoConfig[$value]['pit_open_lid_rising_border'];
		
		$output['profil'][$key] = $profil;	
	}
	
	//Aktor:  from config.php
	$aktor = array('servo','fan_pwm','fan','io','io_pwm');
	if ($thermoConfig['Hardware']['version'] == "miniV2") {
		array_push($aktor,'damper');
	}		
	$output['aktor'] = $aktor;
	
	//IOT: (for now hardcoded)
	$output['iot']['CLon'] = false;
	$output['iot']['CLtoken'] = '';
	$output['iot']['CLint'] = 30;
	
	//Hardware: from config.php
	$output['hardware'] = array('v1','v2','v3', 'miniV2');
	
	//LCD:
	$output['display']['present'] = filter_var($thermoConfig['Display']['lcd_present'],FILTER_VALIDATE_BOOLEAN);
	$output['display']['error_val'] = $thermoConfig['Display']['error_val'];
	$output['display']['lcd_type'] = $thermoConfig['Display']['lcd_type'];
	$output['display']['dim'] = intval($thermoConfig['Display']['dim']);
	$output['display']['timeout'] = intval($thermoConfig['Display']['error_val']);
	$output['display']['start_page'] = $thermoConfig['Display']['start_page'];
	$output['display']['serialspeed'] = intval($thermoConfig['Display']['serialspeed']);
	$output['display']['serialdevice'] = $thermoConfig['Display']['serialdevice'];

	//API:
	$output['api'] = 'version: 2';
	
	//Finish
	return $output;
}

function getSSID(){
	//extract from wifi.php:
	exec('iwconfig wlan0',$return);		//This line is slow!
	preg_match('/ESSID:\"([a-zA-Z0-9\s]+)\"/i',$return[0],$result);
	return $result[1];
}

function getCPUID(){
	if (isset($_SESSION["rpi_cpuid"])){
		$data = $_SESSION["rpi_cpuid"];
	}else{
		exec("cat /proc/cpuinfo | grep Serial | cut -d ' ' -f 2",$return);
		$data = ltrim($return[0],'0');
		$_SESSION["rpi_cpuid"] = $data;
	}
	return $data;
}

?>