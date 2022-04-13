# Lora Packet Forwarder Analyzer

Small tool that logs and extracts packet data from a Lora packet forwarder. 
It has a similar purpose as the [Helium Miner Logs Analyzer](https://github.com/inigoflores/helium-miner-log-analyzer), with the difference that this tool focuses on extracting data directly from the packet forwarder. 
Ths means it can work without the miner being synced or even present in the system, something which will be very usefull with the migration to light hotspots (HIP55).

It consists of two parts: 

* An installable service that captures packet data and stores it in log files (To Do).
* A tool to analyze the generated logs

It currently works on Debian based helium miners. 

## Requirements

The script needs PHP 7, ngrep and gawk to run. On Debian based miners you can install it with:

    sudo apt install php-cli ngrep gawk


## Downloading

You can clone the repository with:

    git clone https://github.com/inigoflores/lora-packet-forwarder-analyzer

## Installation

  To Do....


## Tool usage

    $ ./processlogs.php [-a] [-l] [-s YYYY-MM-DD] [-e YYYY-MM-DD] [-p /FULL/PATH/TO/LOGS]  [-c[FILENAME.CSV]]


    Options

            -a      Show witness statistics

            -l      Show witness list 

            -s      Specify a start date in YYYY-MM-DD format      

            -e      Specify an end date in YYYY-MM-DD format

            -p      Specify a full path to the logs folder

            -c      Create CSV. If no filename is provided, it outputs to stdout

            -d      Include data packets, not only witnesses, in generated lists


## Examples

### Show the stats for all the log files

    $ ./processlogs.php

    Using logs in folder /var/log/packet-forwarder/
    
    Total Packets:         106423
    Total Witnesses:          209
    
          -------------------------------------------------------  
          |        Witnesses        |        Packets               
          -------------------------------------------------------  
    Freq  | Num  | RSSI    | SNR    | Num    | RSSI    | SNR
    -------------------------------------------------------------  
    867.1 |   39 | -118.00 |  -7.00 |   1007 | -125.00 | -12.80
    867.3 |   22 | -119.00 | -16.90 |    365 | -125.00 | -17.00
    867.5 |   21 | -118.00 |  -7.80 |    194 | -124.00 | -17.90
    867.7 |   36 | -121.00 |  -8.80 |    948 | -126.00 | -12.50
    867.9 |   42 | -124.00 | -10.00 |   1044 | -127.00 | -12.80
    868.1 |   20 | -111.50 |  -8.00 |  40459 | -107.00 |  -1.80
    868.3 |   19 | -106.00 |  -7.80 |  62013 | -111.00 |  -7.00
    868.5 |   10 | -101.50 | -20.75 |    393 | -105.00 |  -9.20
    -------------------------------------------------------------  



### Show list of all witnesses between two dates

    $ ./processlogs.php -l -s 2022-02-20 -e 2022-02-21 

    Using logs in folder /var/log/packet-forwarder/
    
    Date                | RSSI | Freq  | SNR   | Noise  | Type
    -------------------------------------------------------------  
    2022-04-08 16:49:29 | -119 | 868.1 |  -6.2 | -112.8 | witness
    2022-04-08 17:15:29 |  -94 | 868.1 |  10.5 | -104.5 | witness
    2022-04-08 17:26:20 | -126 | 867.7 | -11.5 | -114.5 | witness
    2022-04-08 17:39:09 | -127 | 867.7 |   -13 | -114.0 | witness
    2022-04-08 17:41:17 |  -80 | 867.9 |   9.5 |  -89.5 | witness
    2022-04-08 17:52:22 | -117 | 868.3 | -15.2 | -101.8 | witness
    2022-04-08 18:27:35 | -117 | 868.5 | -11.8 | -105.2 | witness
    2022-04-08 18:27:57 | -103 | 867.3 |   6.5 | -109.5 | witness
    2022-04-08 19:13:07 | -119 | 867.1 |  -7.2 | -111.8 | witness
    2022-04-08 19:18:31 | -114 | 867.7 |  -2.5 | -111.5 | witness
    2022-04-08 19:20:30 |  -99 | 868.1 |   8.2 | -107.2 | witness
    2022-04-08 19:34:37 | -118 | 867.9 |  -4.2 | -113.8 | witness
    2022-04-08 19:37:21 | -121 | 867.9 |    -7 | -114.0 | witness
    2022-04-08 19:38:37 |  -87 | 867.7 |  10.5 |  -97.5 | witness
    2022-04-08 19:58:31 | -126 | 867.9 | -11.2 | -114.8 | witness
    2022-04-08 20:21:42 | -108 | 867.9 |   2.8 | -110.8 | witness
    2022-04-08 20:50:07 | -121 | 868.3 |   -19 | -102.0 | witness
    2022-04-08 21:30:18 | -119 | 867.5 | -11.2 | -107.8 | witness
    2022-04-08 21:42:23 |  -60 | 867.5 |     9 |  -69.0 | witness
    2022-04-08 21:54:34 | -103 | 867.7 |   9.2 | -112.2 | witness
    2022-04-08 22:34:15 | -129 | 868.1 | -18.2 | -110.8 | witness
    2022-04-08 22:42:51 | -105 | 867.1 |     6 | -111.0 | witness
    2022-04-08 23:31:00 | -109 | 868.3 | -10.8 |  -98.2 | witness
    2022-04-08 23:38:59 | -126 | 867.3 | -11.8 | -114.2 | witness
    2022-04-09 00:14:26 |  -99 | 867.7 |     5 | -104.0 | witness
    2022-04-09 00:28:04 | -129 | 867.9 | -13.8 | -115.2 | witness
    2022-04-09 00:33:08 |   -7 | 867.5 |  10.8 |  -17.8 | witness
    2022-04-09 01:47:46 | -124 | 868.5 |   -17 | -107.0 | witness
    2022-04-09 01:49:21 | -109 | 867.7 |   4.8 | -113.8 | witness
    2022-04-09 02:07:51 | -107 | 867.1 |   2.2 | -109.2 | witness
    2022-04-09 02:15:20 | -105 | 867.3 |   5.2 | -110.2 | witness
    2022-04-09 02:22:00 | -119 | 867.3 |  -5.8 | -113.2 | witness
    2022-04-09 02:24:59 | -114 | 867.1 |  -2.5 | -111.5 | witness
    2022-04-09 02:26:02 |  -95 | 868.5 |   5.8 | -100.8 | witness
    2022-04-09 02:36:16 | -120 | 867.7 |    -6 | -114.0 | witness
    2022-04-09 02:44:01 | -122 | 867.9 |  -7.2 | -114.8 | witness
    2022-04-09 02:49:54 | -105 | 868.1 |   4.8 | -109.8 | witness
    2022-04-09 03:03:31 | -125 | 867.3 | -13.8 | -111.2 | witness


### Export to CSV

    $ ./processlogs.php -cwitnesses.csv 
    Data saved to witnesses.csv


## To Do

* Extract challenger from data field

