#!/usr/bin/php
<?php
/**
 * processlogs.php
 *
 * Extracts witness data from Helium miner logs
 *
 * @author     Iñigo Flores
 * @copyright  2022 Iñigo Flores
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    0.01
 * @link       https://github.com/inigoflores/helium-miner-log-analyzer
  */

$logsFolder = '/var/log/packet-forwarder/';


$startDate = "2000-01-01";
$endDate = "2030-01-01";
$includeDataPackets = false;

// Command line options
$options = ["d","p:","s:","e:","a","l","c::"];
$opts = getopt(implode("",$options));

// Defaults to stats when called
if (!(isset($opts['l']) || isset($opts['c']))) {
    $opts['a']=true;
}

foreach ($options as $key=>$val){
    $options[$key] = str_replace(":","",$val);
}

uksort($opts, function ($a, $b) use ($options) {
    $pos_a = array_search($a, $options);
    $pos_b = array_search($b, $options);
    return $pos_a - $pos_b;
});

// Handle command line arguments
foreach (array_keys($opts) as $opt) switch ($opt) {
    case 'p':
        $logsFolder = $opts['p'];
        if (substr($logsFolder,strlen($logsFolder)-1) != "/"){
            $logsFolder.="/";
        };
        break;

    case 'd':
        $includeDataPackets = true;
        break;
    case 'e':
        if (!DateTime::createFromFormat('Y-m-d',  $opts['e'])){
            exit("Wrong date format");
        }
        $endDate = $opts['e'];
        break;
    case 'a':
        echo "\nUsing logs in folder {$logsFolder}\n\n";
        $packets = extractData($logsFolder,$startDate,$endDate);
        echo generateStats($packets);
        exit(1);

    case 'l':
        echo "\nUsing logs in folder {$logsFolder}\n\n";
        $packets = extractData($logsFolder,$startDate,$endDate);
        echo generateList($packets,$includeDataPackets);
        exit(1);
        
    case 'c':
        $packets = extractData($logsFolder,$startDate,$endDate);
        $filename = $opts['c'];
        echo generateCSV($packets,$filename,$includeDataPackets);
        exit(1);        
}


/*
 * -------------------------------------------------------------------------------------------------
 * Functions
 * -------------------------------------------------------------------------------------------------
 */

/**
 * @param $logsFolder
 * @return array
 */
function extractData($logsFolder, $startDate = "", $endDate = ""){

    $filenames = glob("{$logsFolder}packet_forwarder*.log*");

    //print_r("{$logsFolder}packet_forwarder*.log*");
    if (empty($filenames)){
        exit ("No logs found. Install the service and let it run for some time before running this command again.\n");
    }

    rsort($filenames); //Order is important, from older to more recent.

    foreach ($filenames as $filename) {

        $buf = file_get_contents($filename,);
        if (substr($filename, -3) == '.gz') {
            $buf = gzdecode($buf);
        }

        $lines = explode("\n", $buf);
        unset($buf);

        $packets = [];
        foreach ($lines as $line) {

            if (!strstr($line,'rxpk')) { //empty line
                continue;
            }



            $temp = explode('{"rxpk":', $line);
            $temp1 = explode(" ",$temp[0]);
            $datetime = "{$temp1[0]} $temp1[1]";

            if ($datetime < $startDate || $datetime > $endDate) {
                continue;
            }
            //echo strstr($fields[2], '{"rxpk"');die();

            //echo '{"rxpk":' . $temp[1];
            $packet = json_decode('{"rxpk":' . $temp[1]);

            if (empty($packet)) {
                 continue;
            }
            $packet = $packet->rxpk[0];
            // print_r($packet);//die();

            if (isset($packet->rssis)) {
                $rssi = $packet->rssis;
            } else {
                $rssi = $packet->rssi;
            }

            if (strstr($packet->data,"QDD")) {
                $type = "witness";
            } else {
                $type = "data";
            }

            $snr = $packet->lsnr;
            $freq = $packet->freq;

            $packets[] = compact('datetime', 'freq', 'rssi', 'snr', 'type');
        }
    }
    return $packets;
}


/**
 * @param $packets
 * @return string
 */
function generateStats($packets) {

    if (empty($packets)) {
        exit("No packets found\n");
    }

    $startTime = DateTime::createFromFormat('Y-m-d H:i:s',explode('.',$packets[0]['datetime'])[0]);
    $endTime = DateTime::createFromFormat('Y-m-d H:i:s',explode('.',end($packets)['datetime'])[0]);
    $intervalInHours = ($endTime->getTimestamp() - $startTime->getTimestamp())/3600;

    $totalWitnesses = 0;
    $totalPackets = sizeOf($packets);
    $lowestWitnessRssi = $lowestPacketRssi = 0;

    foreach ($packets as $packet){

        //echo $packet['freq'] . "\n";
        //@$freqs["{$packet['freq']}"]++;
        $packetDataByFrequency["{$packet['freq']}"]['rssi'][] = $packet['rssi'];
        $packetDataByFrequency["{$packet['freq']}"]['snr'][] = $packet['snr'];

        if ($packet['rssi'] < $lowestPacketRssi) {
            $lowestPacketRssi = $packet['rssi'];
        }

        if ($packet['type']=='witness') {
            $totalWitnesses++;
            $witnessDataByFrequency["{$packet['freq']}"]['rssi'][] = $packet['rssi'];
            $witnessDataByFrequency["{$packet['freq']}"]['snr'][] = $packet['snr'];

            if ($packet['rssi'] < $lowestWitnessRssi) {
                $lowestWitnessRssi = $packet['rssi'];
            }
        }
    }
    foreach ($packetDataByFrequency as $freq => $rssifreq) {
        $packetRssiAverages["{$freq}"] = number_format(getMedian($packetDataByFrequency["{$freq}"]['rssi']),2);
        $packetSnrAverages["{$freq}"] =  number_format(getMedian($packetDataByFrequency["{$freq}"]['snr']),2);
    }

    foreach ($witnessDataByFrequency as $freq => $rssifreq) {
        $witnessRssiAverages["{$freq}"] = number_format(getMedian($witnessDataByFrequency["{$freq}"]['rssi']) ,2);
        $witnessSnrsAverages["{$freq}"] =  number_format(getMedian($witnessDataByFrequency["{$freq}"]['snr']),2);
    }

    $freqs = array_keys($packetDataByFrequency);
    sort($freqs);

    $totalPacketsPerHour = round($totalPackets / $intervalInHours,2);
    $totalWitnessesPerHour = round($totalWitnesses / $intervalInHours,2);

    $totalPacketsPerHour = str_pad("($totalPacketsPerHour)",9, " ", STR_PAD_LEFT);;
    $totalWitnessesPerHour = str_pad("($totalWitnessesPerHour)",9, " ", STR_PAD_LEFT);;


    $totalWitnesses = str_pad($totalWitnesses,7, " ", STR_PAD_LEFT);
    $totalPackets = str_pad($totalPackets,7, " ", STR_PAD_LEFT);
    $lowestPacketRssi = str_pad($lowestPacketRssi,7," ",STR_PAD_LEFT);
    $lowestWitnessRssi = str_pad($lowestWitnessRssi,7," ",STR_PAD_LEFT);


    $output = "";
    $output.= "Total Packets:        $totalPackets $totalPacketsPerHour/hour\n";
    $output.= "Total Witnesses:      $totalWitnesses $totalWitnessesPerHour/hour \n";
    $output.= "Lower Packet RSSI:    $lowestPacketRssi dBm\n";
    $output.= "Lower Witness RSSI:   $lowestWitnessRssi dBm\n";
    $output.= "\n";
    $output.= "      -------------------------------------------------------  "  . PHP_EOL;
    $output.= "      |        Witnesses        |        Packets               " . PHP_EOL;
    $output.= "      -------------------------------------------------------  " . PHP_EOL;
    $output.= "Freq  | Num  | RSSI    | SNR    | Num    | RSSI    | SNR       " . PHP_EOL;
    $output.= "-------------------------------------------------------------  " . PHP_EOL;

    foreach ($freqs as $freq) {
        $numberOfWitnesses = str_pad(count($witnessDataByFrequency[$freq]['rssi']), 4, " ", STR_PAD_LEFT);
        $witnessRssi = str_pad($witnessRssiAverages["{$freq}"] , 7, " ", STR_PAD_LEFT);
        $witnessSnr = str_pad($witnessSnrsAverages["{$freq}"] , 6, " ", STR_PAD_LEFT);

        $numberOfPackets = str_pad(count($packetDataByFrequency[$freq]['rssi']), 6, " ", STR_PAD_LEFT);
        $packetRssi = str_pad($packetRssiAverages["{$freq}"] , 7, " ", STR_PAD_LEFT);
        $packetSnr = str_pad($packetSnrAverages["{$freq}"] , 6, " ", STR_PAD_LEFT);

        $output.= "$freq | $numberOfWitnesses | $witnessRssi | $witnessSnr | $numberOfPackets | $packetRssi | $packetSnr " . PHP_EOL;
    };
    $output.= "-------------------------------------------------------------  " . PHP_EOL;

    echo $output;
}


/**
 * @param $packets
 * @param $includeDataPackets
 * @return string
 */
function generateList($packets, $includeDataPackets = false) {
    $output = "Date                | RSSI | Freq  | SNR   | Noise  | Type    \n";
    $output.= "-------------------------------------------------------------  \n";

    echo $includeDataPackets;

    foreach ($packets as $packet){
        if ($packet['type']!="witness" && !$includeDataPackets){
            continue;
        }

        $rssi = str_pad($packet['rssi'], 4, " ", STR_PAD_LEFT);
        $snr = str_pad($packet['snr'], 5, " ", STR_PAD_LEFT);
        $noise = str_pad(number_format((float) ($packet['rssi'] - $packet['snr']),1),6,  " ", STR_PAD_LEFT);
        $type = str_pad($packet['type'],6,  " ", STR_PAD_LEFT);
        //$challenger = @str_pad($beacon['challenger'],52, " ", STR_PAD_RIGHT);
        $challenger="";
        $output.=@"{$packet['datetime']} | {$rssi} | {$packet['freq']} | {$snr} | {$noise} | $type \n";

    }
    return $output;
}


/**
 * @param $packets
 * @param $includeDataPackets
 * @return string
 */
function generateCSV($packets, $filename = false, $includeDataPackets = false) {
    $columns = ['Date','Freq','RSSI','SNR','Noise','Type'];
    $data = array2csv($columns);
    foreach ($packets as $packet){
        if ($packet['type']!="witness" && !$includeDataPackets){
            continue;
        }

        $noise = number_format((float) ($packet['rssi'] - $packet['snr']),1);
        $data.= @array2csv([
            $packet['datetime'], $packet['freq'], $packet['rssi'], $packet['snr'], $noise, $packet['type']]
        );
    }

    if ($filename) {
        $data = "SEP=;" . $data;
        file_put_contents($filename,$data);
        return "Data saved to $filename\n";
    }

    return $data;

}

/**
 * @param $fields
 * @param string $delimiter
 * @param string $enclosure
 * @param string $escape_char
 * @return false|string
 */
function array2csv($fields, $delimiter = ",", $enclosure = '"', $escape_char = '\\')
{
    $buffer = fopen('php://temp', 'r+');
    fputcsv($buffer, $fields, $delimiter, $enclosure, $escape_char);
    rewind($buffer);
    $csv = fgets($buffer);
    fclose($buffer);
    return $csv;
}

function getMedian($arr) {
    sort($arr);
    $count = count($arr);
    $middleval = floor(($count-1)/2);
    if ($count % 2) {
        $median = $arr[$middleval];
    } else {
        $low = $arr[$middleval];
        $high = $arr[$middleval+1];
        $median = (($low+$high)/2);
    }
    return $median;
}

function getMean($arr) {
    $count = count($arr);
    return array_sum($arr)/$count;
}
