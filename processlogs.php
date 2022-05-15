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
 * @version    0.02
 * @link       https://github.com/inigoflores/lora-packet-forwarder-analyzer
  */

$logsPath = '/var/log/packet-forwarder/packet_forwarder.log';


$startDate = "2000-01-01";
$endDate = "2030-01-01";
$includeDataPackets = false;

// Command line options
$options = ["d","p:","s:","e:","c::","a","l","i"];
$opts = getopt(implode("",$options));

// Defaults to stats when called
if (!(isset($opts['l']) || isset($opts['c']) || isset($opts['i']))) {
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

$csvOutput = false;

// Handle command line arguments
foreach (array_keys($opts) as $opt) switch ($opt) {
    case 'p':
        $logsPath = $opts['p'];
        if (substr($logsPath,strlen($logsPath)-1) != "/" && is_dir($logsPath)){
            $logsPath.="/";
        };
        break;
    case 'd':
        $includeDataPackets = true;
        break;
    case 's':
        if (!DateTime::createFromFormat('Y-m-d',  $opts['s'])){
            exit("Wrong date format");
        }
        $startDate = $opts['s'];
        break;
    case 'e':
        if (!DateTime::createFromFormat('Y-m-d',  $opts['e'])){
            exit("Wrong date format");
        }
        $endDate = $opts['e'];
        break;
    case 'c':
        $csvOutput = true;
        $filename = $opts['c'];
        break;
    case 'a':
        echo "\nUsing logs in {$logsPath}\n\n";
        $packets = extractData($logsPath,$startDate,$endDate);
        echo generateStats($packets);
        exit(1);
    case 'l':
        echo "\nUsing logs in {$logsPath}\n\n";
        $packets = extractData($logsPath,$startDate,$endDate);
        if (!$csvOutput) {
            echo generateList($packets,$includeDataPackets);
        } else {
            echo generateCSV($packets,$filename,$includeDataPackets);
        }
        exit(1);
    case 'i':
        echo "\nUsing logs in {$logsPath}\n\n";
        $packets = extractData($logsPath,$startDate,$endDate);
        $histogram = generateHistogramData($packets,$includeDataPackets);
        if (!$csvOutput) {
            echo generateHistogramASCIIChart($histogram,$includeDataPackets);
        } else {
            echo generateCSVHistogram($histogram,$filename,$includeDataPackets);
        }
        exit(1);

}


/*
 * -------------------------------------------------------------------------------------------------
 * Functions
 * -------------------------------------------------------------------------------------------------
 */

/**
 * @param $logsPath
 * @return array
 */
function extractData($logsPath, $startDate = "", $endDate = ""){

    if (is_dir($logsPath)) {
        $filenames = glob("{$logsPath}packet_forwarder*.log*");
    } else if (is_file($logsPath)) {
        $filenames = [$logsPath];
    } else {
        exit ("Path is not a valid folder or file.\n");
    }

    if (empty($filenames)){
        exit ("No logs found. Install the service and let it run for some time before running this command again.\n");
    }

    rsort($filenames); //Order is important, from older to more recent.

    $packets = [];

    foreach ($filenames as $filename) {

        $buf = file_get_contents($filename,);
        if (substr($filename, -3) == '.gz') {
            $buf = gzdecode($buf);
        }

        $lines = explode("\n", $buf);
        unset($buf);

        foreach ($lines as $line) {

            if (!strpos($line,'xpk"')) { //empty line
                continue;
            }

            $jsonStart = strpos($line,"{");
            $jsonData = substr($line,$jsonStart);
            //$temp = explode('{"rxpk":', $line);
            $temp = explode(" ",substr($line,0,$jsonStart));
            $datetime = "{$temp[0]} $temp[1]";

            if ($datetime < $startDate || $datetime > $endDate) {
                continue;
            }

            $packet = json_decode($jsonData);


            if (empty($packet)) {
                 continue;
            }

            if (isset($packet->rxpk)) {
                $packet = $packet->rxpk[0];
                $decodedData = base64_decode($packet->data);

                if (isset($packet->rssis)) {
                    $rssi = $packet->rssis;
                } else {
                    $rssi = $packet->rssi;
                }

                if (substr($packet->data, 0, 3) == "QDD" && strlen($decodedData) == 52) {
                    $type = "witness";
                } else {
                    $type = "rx data";
                }

                $snr = $packet->lsnr;
                $freq = $packet->freq;

            } else if (isset($packet->txpk))  { //Sent beacon

                $packet = $packet->txpk;
                $decodedData = base64_decode($packet->data);

                $rssi = $packet->powe;

                if (substr($packet->data, 0, 3) == "QDD" && strlen($decodedData) == 52) {
                    $type = "beacon";
                } else {
                    $type = "tx data";
                }
                $freq = $packet->freq;
                $snr = "";
            }

            if ($type=='witness' || $type=='beacon') {
                //LongFi packet. The Onion Compact Key starts at position 12 and is 33 bytes long. THanks to @ricopt5 for helping me figure this out.
                $onionCompactKey = substr($decodedData, 12, 33);
                $hash = base64url_encode(hash('sha256', $onionCompactKey, true)); // This is the Onion Key Hash
            } else {
                $hash = base64url_encode(hash('crc32b', $decodedData, true)); //
            }
            //
            $packets[] = compact('datetime', 'freq', 'rssi', 'snr', 'type', 'hash');


        }
    }

    //Sort packets by datetime
    usort($packets, function($a, $b) {
        return $a['datetime'] <=> $b['datetime'];
    });

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

    $systemDate = new DateTime();

    $startTime = DateTime::createFromFormat('Y-m-d H:i:s',$packets[0]['datetime'], new DateTimeZone( 'UTC' ));
    $endTime = DateTime::createFromFormat('Y-m-d H:i:s',end($packets)['datetime'], new DateTimeZone( 'UTC' ));
    $intervalInHours = ($endTime->getTimestamp() - $startTime->getTimestamp())/3600;
    $intervalInDays = ($endTime->getTimestamp() - $startTime->getTimestamp())/3600/24;

    $startTime->setTimezone($systemDate->getTimezone());
    $endTime->setTimezone($systemDate->getTimezone());

    $totalWitnesses = $totalBeacons = 0;
    $totalPackets = sizeOf($packets);
    $lowestWitnessRssi = $lowestPacketRssi = 0;

    $witnessDataByFrequency = [];
    foreach ($packets as $packet){

        if ($packet['type']=='tx data') {
            continue;

        } else if ($packet['type']=='beacon') {
            $totalBeacons++;
            continue;
        }

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
        $packetRssiAverages["{$freq}"] = number_format(getMean($packetDataByFrequency["{$freq}"]['rssi']),2);
        $packetRssiMins["{$freq}"] = number_format(min($packetDataByFrequency["{$freq}"]['rssi']),2);
        $packetSnrAverages["{$freq}"] =  number_format(getMean($packetDataByFrequency["{$freq}"]['snr']),2);
    }

    foreach ($witnessDataByFrequency as $freq => $rssifreq) {
        $witnessRssiAverages["{$freq}"] = number_format(getMean($witnessDataByFrequency["{$freq}"]['rssi']) ,2);
        $witnessRssiMins["{$freq}"] = number_format(min($witnessDataByFrequency["{$freq}"]['rssi']) ,2);
        $witnessSnrsAverages["{$freq}"] =  number_format(getMean($witnessDataByFrequency["{$freq}"]['snr']),2);
    }

    $freqs = array_keys($packetDataByFrequency);
    sort($freqs);

    $totalPacketsPerHour = number_format(round($totalPackets / $intervalInHours,2),2,".","");
    $totalWitnessesPerHour = number_format(round($totalWitnesses / $intervalInHours,2), 2,".","");
    $totalBeaconsPerDay = number_format(round($totalBeacons / $intervalInDays,2), 2,".","");

    $totalPacketsPerHour = str_pad("($totalPacketsPerHour",9, " ", STR_PAD_LEFT);;
    $totalWitnessesPerHour = str_pad("($totalWitnessesPerHour",9, " ", STR_PAD_LEFT);;
    $totalBeaconsPerDay = str_pad("($totalBeaconsPerDay",9, " ", STR_PAD_LEFT);;

    $totalWitnesses = str_pad($totalWitnesses,7, " ", STR_PAD_LEFT);
    $totalBeacons = str_pad($totalBeacons,7, " ", STR_PAD_LEFT);
    $totalPackets = str_pad($totalPackets,7, " ", STR_PAD_LEFT);
    $lowestPacketRssi = str_pad($lowestPacketRssi,7," ",STR_PAD_LEFT);
    $lowestWitnessRssi = str_pad($lowestWitnessRssi,7," ",STR_PAD_LEFT);
    $intervalInHoursStr = round($intervalInHours,1);

    $output = "First Packet:        " . $startTime->format("d-m-Y H:i:s") . PHP_EOL;
    $output.= "Last Packet:         " . $endTime->format("d-m-Y H:i:s") . " ($intervalInHoursStr hours)" . PHP_EOL . PHP_EOL;
    $output.= "Total Witnesses:      $totalWitnesses $totalWitnessesPerHour/hour)\n";
    $output.= "Total Packets:        $totalPackets $totalPacketsPerHour/hour)\n";
    $output.= "Total Beacons:        $totalBeacons $totalBeaconsPerDay/day)\n";
    $output.= "Lowest Witness RSSI:  $lowestWitnessRssi dBm\n";
    $output.= "Lowest Packet RSSI:   $lowestPacketRssi dBm\n";
    $output.= "\n";
    $output.= "      -----------------------------------------------------------------------------  "  . PHP_EOL;
    $output.= "      |        Witnesses                    |          All Packets              " . PHP_EOL;
    $output.= "      -----------------------------------------------------------------------------  " . PHP_EOL;
    $output.= "Freq  | Num  | RSSI Avg | RSSI Min | SNR    | Num    | RSSI Avg | RSSI Min | SNR       " . PHP_EOL;
    $output.= "-----------------------------------------------------------------------------------  " . PHP_EOL;

    foreach ($freqs as $freq) {
        $numberOfWitnesses = @str_pad(count($witnessDataByFrequency[$freq]['rssi']), 4, " ", STR_PAD_LEFT);
        $witnessRssi = @str_pad($witnessRssiAverages["{$freq}"] , 7, " ", STR_PAD_LEFT);
        $witnessSnr = @str_pad($witnessSnrsAverages["{$freq}"] , 6, " ", STR_PAD_LEFT);
        $witnessRssiMin = @str_pad($witnessRssiMins["{$freq}"] , 7, " ", STR_PAD_LEFT);

        $numberOfPackets = str_pad(count($packetDataByFrequency[$freq]['rssi']), 6, " ", STR_PAD_LEFT);
        $packetRssi = str_pad($packetRssiAverages["{$freq}"] , 7, " ", STR_PAD_LEFT);
        $packetSnr = str_pad($packetSnrAverages["{$freq}"] , 6, " ", STR_PAD_LEFT);
        $packetRssiMin = str_pad($packetRssiMins["{$freq}"] , 7, " ", STR_PAD_LEFT);

        $output.= "$freq | $numberOfWitnesses |  $witnessRssi |  $witnessRssiMin | $witnessSnr | $numberOfPackets |  $packetRssi |  $packetRssiMin | $packetSnr " . PHP_EOL;
    };
    $output.= "------------------------------------------------------------------------------------  " . PHP_EOL;

    echo $output;
}


/**
 * @param $packets
 * @param $includeDataPackets
 * @return string
 */
function generateList($packets, $includeDataPackets = false) {

    $systemDate = new DateTime();
    $utc = new DateTimeZone( 'UTC' );

    $header = "Date                | Freq  | RSSI | SNR   | Noise  | Type    | Hash";
    $separator = "-------------------------------------------------------------------------------------------------------------";
    $output="";

    foreach ($packets as $packet){
        if (($packet['type']=="tx data" || $packet['type']=="rx data") && !$includeDataPackets){
            continue;
        }

        $datetime = DateTime::createFromFormat('Y-m-d H:i:s',$packet['datetime'], $utc);
        $datetime->setTimezone($systemDate->getTimezone());

        $rssi = str_pad($packet['rssi'], 4, " ", STR_PAD_LEFT);

        if ($packet['type']=="witness"||$packet['type']=="rx data"){
            $noise = number_format((float)($packet['rssi'] - $packet['snr']));
        } else {
            $noise = "";
        }

        $snrStr = str_pad($packet['snr'], 5, " ", STR_PAD_LEFT);
        $noiseStr = str_pad($noise,  6, " ", STR_PAD_LEFT);
        $type = str_pad($packet['type'],7,  " ", STR_PAD_LEFT);
        $hash = @str_pad($packet['hash'],44, " ", STR_PAD_RIGHT);
        $datetimeStr = $datetime->format("d-m-Y H:i:s");
        $output.=@"$datetimeStr | {$packet['freq']} | {$rssi} | {$snrStr} | {$noiseStr} | $type | $hash" . PHP_EOL;
    }
    return $header . PHP_EOL . $separator . PHP_EOL . $output;
}


/**
 * @param $packets
 * @param $includeDataPackets
 * @return string
 */
function generateCSV($packets, $filename = false, $includeDataPackets = false) {

    $columns = ['Date','Freq','RSSI','SNR','Noise','Type','Hash'];
    $data = array2csv($columns);
    foreach ($packets as $packet){
        if (($packet['type']=="tx data" || $packet['type']=="rx data") && !$includeDataPackets){
            continue;
        }

        if (!empty($packet['snr'])){
            $noise = number_format((float) ($packet['rssi'] - $packet['snr']),1);
        } else {
            $noise = "";
        }
        $data.= @array2csv([
                $packet['datetime'], $packet['freq'], $packet['rssi'], $packet['snr'], $noise, $packet['type'], $packet['hash']]
        );
    }

    if ($filename) {
        $data = "SEP=," . PHP_EOL . $data;
        file_put_contents($filename,$data);
        return "Data saved to $filename\n";
    }

    return $data;
}


/**
 * @param $packets
 * @param $includeDataPackets
 * @return string
 */
function generateHistogramData($packets, $includeDataPackets = false) {

    $systemDate = new DateTime();
    $utc = new DateTimeZone( 'UTC' );
    $histogram=[];

    foreach ($packets as $packet){
        if (($packet['type']=="tx data" || $packet['type']=="rx data") && !$includeDataPackets){
            continue;
        }

        $datetime = DateTime::createFromFormat('Y-m-d H:i:s',$packet['datetime'], $utc);
        $datetime->setTimezone($systemDate->getTimezone());

        $hour = (int) ($datetime->getTimestamp()/3600)*3600;
        $datetime->setTimestamp($hour);
        $hour = $datetime->format("d-m-Y H:i");
        //echo $hour . "\n";
        $histogram[$hour] = @$histogram[$hour] + 1;

    }
    return $histogram;
}

/**
 * @param $packets
 * @param $includeDataPackets
 * @return string
 */
function generateHistogramASCIIChart($histogramData)
{
    $output = "";

    $maxValue = max($histogramData);

    foreach ($histogramData as $date => $number){
        $output.= "$date ";
        for ($i=0; $i < $number/$maxValue*80; $i++) {
            $output.= "■";
        }
        $output.= " $number" . PHP_EOL;
    }

    return $output;
}

/**
 * @param $packets
 * @param $includeDataPackets
 * @return string
 */
function generateCSVHistogram($histogramData, $filename = false) {

    $columns = ['Date', 'Items'];

    $data = array2csv($columns);
    foreach ($histogramData as $date => $number){
        $data.= @array2csv([$date,$number]);
    }

    if ($filename) {
        $data = "SEP=," . PHP_EOL . $data;
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

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}