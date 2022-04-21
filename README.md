# Lora Packet Forwarder Analyzer

Small tool that logs and extracts packet data from a Lora packet forwarder. 
It has a similar purpose as the [Helium Miner Logs Analyzer](https://github.com/inigoflores/helium-miner-log-analyzer), with the difference that this tool focuses on extracting data directly from the packet forwarder. 
Ths means it can work without the miner being synced or even present in the system, something which will be very usefull with the migration to light hotspots (HIP55).

It consists of two parts: 

* An installable service that captures packet data and stores it in log files.
* A tool to analyze the generated logs

It currently works on Debian based helium miners. 

As the moment it can't extract the challenger for received witnesses, only the Onion Key Hash (useful to be able to identify witnesses when comparing two miners side by side). A lookup in the blockchain needs to be performed to obtain the PoC transaction, and from there the challenger.


## Requirements

The script needs PHP 7, ngrep and gawk to run. On Debian based miners you can install it with:

    sudo apt install php-cli ngrep gawk

These packages will be automatically installed when installing the service below.

## Downloading

You can clone the repository with:

    git clone https://github.com/inigoflores/lora-packet-forwarder-analyzer

## Installation

To install the service, run:

    ./install.sh

Packet logging will start straight away in /var/log/packet-forwarder/packet_forwarder.log  

You can uninstall the service by running:

    ./uninstall.sh

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

    Total Witnesses:          208     (2.21/hour)
    Total Packets:         106423  (1129.60/hour)
    Lowest Witness RSSI:     -137 dBm
    Lowest Packet RSSI:      -139 dBm
    
          -----------------------------------------------------------------------------  
          |        Witnesses                    |          All Packets              
          -----------------------------------------------------------------------------  
    Freq  | Num  | RSSI Avg | RSSI Min | SNR    | Num    | RSSI Avg | RSSI Min | SNR
    -----------------------------------------------------------------------------------  
    867.1 |   39 |  -118.00 |  -136.00 |  -7.75 |   1007 |  -124.57 |  -137.00 | -12.62
    867.3 |   22 |  -110.68 |  -131.00 | -12.36 |    365 |  -124.54 |  -138.00 | -16.60
    867.5 |   20 |  -112.05 |  -125.00 |  -7.16 |    194 |  -123.16 |  -137.00 | -16.60
    867.7 |   36 |  -119.33 |  -135.00 |  -7.78 |    948 |  -126.10 |  -139.00 | -12.41
    867.9 |   42 |  -117.31 |  -137.00 |  -7.85 |   1044 |  -126.74 |  -139.00 | -12.47
    868.1 |   20 |  -109.65 |  -123.00 |  -7.15 |  40459 |  -106.33 |  -130.00 |  -1.42
    868.3 |   19 |  -101.95 |  -122.00 |  -6.32 |  62013 |  -110.35 |  -131.00 |  -6.63
    868.5 |   10 |  -101.90 |  -112.00 | -19.67 |    393 |  -105.17 |  -121.00 |  -9.58
    ------------------------------------------------------------------------------------  




### Show list of all witnesses between two dates

    $ ./processlogs.php -l -s 2022-02-20 -e 2022-02-21 

    Using logs in folder /var/log/packet-forwarder/

    Date                | RSSI | Freq  | SNR   | Noise  | Type    | Hash
    -------------------------------------------------------------------------------------------------------------
    2022-04-08 16:49:29 | -119 | 868.1 |  -6.2 | -112.8 | witness | ycWNRqQ4-5qwSXR7YpAd-2lURKHm7n5D7eoWQvzLgbY
    2022-04-08 17:15:29 |  -94 | 868.1 |  10.5 | -104.5 | witness | JJgZlfvz2kK_ZJY4-Mbj93JDxx4RZdIZyJQjWLeO-uc
    2022-04-08 17:26:20 | -126 | 867.7 | -11.5 | -114.5 | witness | qtoR1Q4kW_FWlvHjxkl8JbuHl4auUPaS1i_VvcerPsI
    2022-04-08 17:39:09 | -127 | 867.7 |   -13 | -114.0 | witness | ouvUWjieCan58f_Cc89GzyOpQ_ccGx4FvQRPGrQrmr4
    2022-04-08 17:41:17 |  -80 | 867.9 |   9.5 |  -89.5 | witness | TMfkdMNdzoV_VpzP6ihLl_mcHmV5gk8rQMCv3sNOZ84
    2022-04-08 17:52:22 | -117 | 868.3 | -15.2 | -101.8 | witness | nTr3nNiBbwFNoUwogs1YOHL-UGPfT3sR839OIocSfiU
    2022-04-08 18:27:35 | -117 | 868.5 | -11.8 | -105.2 | witness | 4jMowRY28Nfe01ZVRSlnLa_mMLqTrxdqw1GtyFJ9W8k
    2022-04-08 18:27:57 | -103 | 867.3 |   6.5 | -109.5 | witness | uZKV6Y4fUiExw4ZKvuPoCLYOoouUzs1HD1yPqwkMdOE
    2022-04-08 19:13:07 | -119 | 867.1 |  -7.2 | -111.8 | witness | s5bjhrFMbvhvBafqJBiAtPTD2CLLhxl-qsQfZPkD85c
    2022-04-08 19:18:31 | -114 | 867.7 |  -2.5 | -111.5 | witness | wKB5psByGlO-gZDpAR8r3UmTjzSgiEPQaxvjk2IMErc
    2022-04-08 19:20:30 |  -99 | 868.1 |   8.2 | -107.2 | witness | zxEzGghhqQaaf_OoYPbsLpu33853lwrqtaWw5KY1x0Q
    2022-04-08 19:34:37 | -118 | 867.9 |  -4.2 | -113.8 | witness | zbWn5Hp5tEtOGLDFBHmX6HJSQSmRq0ORAYxuj9f3nhU
    2022-04-08 19:37:21 | -121 | 867.9 |    -7 | -114.0 | witness | pR_qugoiZp8sLoDuQ0-vitdZuvxqYL18fYEXVRIlraM
    2022-04-08 19:38:37 |  -87 | 867.7 |  10.5 |  -97.5 | witness | pUn-zdMOoc1awQJlXbKNITnxMZeBpRfvopg-2wn0KAA
    2022-04-08 19:58:31 | -126 | 867.9 | -11.2 | -114.8 | witness | V_4ObJxs20yb_DcGwINpOiaNDtQ-6Sw8-XxOAzI6868
    2022-04-08 20:21:42 | -108 | 867.9 |   2.8 | -110.8 | witness | -h_t-o6difeFvzjOHRaKTFcA7ke5mtH2_s8Shou3lWQ
    2022-04-08 20:50:07 | -121 | 868.3 |   -19 | -102.0 | witness | C8Y86x0jYWrTbOx7V_OLH61oBeCzQ7JzwxjeJaWMQvI
    2022-04-08 21:30:18 | -119 | 867.5 | -11.2 | -107.8 | witness | sIsmA5mmYu2ah8mB9m-IOwrTM19BeIZFO3VNApxrbJU
    2022-04-08 21:42:23 |  -60 | 867.5 |     9 |  -69.0 | witness | 2A6yyeKn8JMIIUTzSARWLKUMXlUbyAeRGLvu3242d48
    2022-04-08 21:54:34 | -103 | 867.7 |   9.2 | -112.2 | witness | 4-vw5lPC1033gsrmDRknK7cmyW9Bu9EM56kFZuq1IGk
    2022-04-08 22:34:15 | -129 | 868.1 | -18.2 | -110.8 | witness | D9NpMoKzcaseIvU5JX1UatWzaOwxHVmu1npUFXzxXIs
    2022-04-08 22:42:51 | -105 | 867.1 |     6 | -111.0 | witness | Y_a5HE9kyFbhp90c9CGqrPFsFC2gAbI4NkBEOZ4FYYQ
    2022-04-08 23:31:00 | -109 | 868.3 | -10.8 |  -98.2 | witness | sdxOp3REWcC8Mb6-M_RLntdEMNeiv1-VVfhPgzl4UbU
    2022-04-08 23:38:59 | -126 | 867.3 | -11.8 | -114.2 | witness | dq7B03tc_HlURg6VqzMQ8uM-0GR4k8g6yRhNTMfDJAk
    2022-04-09 00:14:26 |  -99 | 867.7 |     5 | -104.0 | witness | c1EsqLzUx2B7VW7-Hcbe_JZ--ijXiGCS-kRLLpb_3e8
    2022-04-09 00:28:04 | -129 | 867.9 | -13.8 | -115.2 | witness | DE-oi3KvotNT9ysUc1FwMMzzwZqfKZRQ1sPoCYcRvLE
    2022-04-09 00:33:08 |   -7 | 867.5 |  10.8 |  -17.8 | witness | s_pJJVOUcqiRhOf4uw4Q0qv0mQPApyNlK0JMqHGGmHk
    2022-04-09 01:47:46 | -124 | 868.5 |   -17 | -107.0 | witness | y2HTCIMKJtZZv_A4cXCIB-HUEAPj3iyy0OepakpHGGs
    2022-04-09 01:49:21 | -109 | 867.7 |   4.8 | -113.8 | witness | ZsudG_3Z8ofAXdD2jQqvnSkZGykwhwbq9ELX4VR-YFI
    2022-04-09 02:07:51 | -107 | 867.1 |   2.2 | -109.2 | witness | f0vTrpEIZRnB1iRvMXu1Tl70b9iI5jufaivg0vb9K2g
    2022-04-09 02:15:20 | -105 | 867.3 |   5.2 | -110.2 | witness | -NuZQllBe97vMGlfQLzDrUHw7s4vQLMa8aH8mpV6taE
    2022-04-09 02:22:00 | -119 | 867.3 |  -5.8 | -113.2 | witness | iyNv8NiXgpwxmOgXkRSkRB2EJ13umS26S1m8BQZSg9U
    2022-04-09 02:24:59 | -114 | 867.1 |  -2.5 | -111.5 | witness | sfZr2sv-PSldBmtaPuYq9CnwItp-MM8Dq9lUyNlpBj0
    2022-04-09 02:26:02 |  -95 | 868.5 |   5.8 | -100.8 | witness | 8Pwq2ny6x5PrleEGZtXAl3oYmZjB_sze4fGtCaro8ZU
    2022-04-09 02:36:16 | -120 | 867.7 |    -6 | -114.0 | witness | GIU2to9g6mMkmWmA-yDkm5jHw1uSqxYoLRbePBf0cSc
    2022-04-09 02:44:01 | -122 | 867.9 |  -7.2 | -114.8 | witness | U2mBEjAs8Qum3Pi8Iom-N7jzl46TFnuBODNAleFosm0


## Show list of all packets received

    ./processlogs.php -ld 
    
    Using logs in /var/log/packet-forwarder/
    
    Date                | Freq  | RSSI | SNR   | Noise  | Type    | Hash
    -------------------------------------------------------------------------------------------------------------
    2022-04-08 16:49:17 | 868.1 | -108 |  -3.2 | -104.8 |    data | LWCAGQ                                      
    2022-04-08 16:49:17 | 868.3 | -113 |    -6 | -107.0 |    data | spbt8g                                      
    2022-04-08 16:49:17 | 868.3 | -112 |  -4.2 | -107.8 |    data | spbt8g                                      
    2022-04-08 16:49:18 | 868.3 | -105 |   0.2 | -105.2 |    data | LWCAGQ                                      
    2022-04-08 16:49:18 | 868.3 | -102 |   2.8 | -104.8 |    data | LvT-LA                                      
    2022-04-08 16:49:18 | 868.5 | -105 |    -2 | -103.0 |    data | LWCAGQ                                      
    2022-04-08 16:49:19 | 868.1 | -110 |  -3.5 | -106.5 |    data | LWCAGQ                                      
    2022-04-08 16:49:20 | 868.3 | -106 |  -1.8 | -104.2 |    data | LWCAGQ                                      
    2022-04-08 16:49:20 | 868.1 |  -91 |   7.5 |  -98.5 |    data | G-geEA                                      
    2022-04-08 16:49:20 | 868.1 |  -93 |   7.8 | -100.8 |    data | G-geEA                                      
    2022-04-08 16:49:20 | 868.1 |  -91 |   7.8 |  -98.8 |    data | TbK5lg                                      
    2022-04-08 16:49:20 | 868.5 | -107 |  -2.2 | -104.8 |    data | LWCAGQ                                      
    2022-04-08 16:49:20 | 868.1 |  -91 |   8.2 |  -99.2 |    data | N9n91w                                      
    2022-04-08 16:49:23 | 868.3 | -110 |  -6.5 | -103.5 |    data | _F4QpQ                                      
    2022-04-08 16:49:24 | 868.1 | -115 |    -2 | -113.0 |    data | 84SyvA                                      
    2022-04-08 16:49:25 | 868.3 | -114 |    -6 | -108.0 |    data | 84SyvA                                      
    2022-04-08 16:49:25 | 868.3 | -109 |  -2.8 | -106.2 |    data | J64Htw                                      
    2022-04-08 16:49:26 | 868.1 | -118 |  -5.5 | -112.5 |    data | 84SyvA                                      
    2022-04-08 16:49:26 | 868.1 |  -93 |   7.8 | -100.8 |    data | x_JSqA                                      
    2022-04-08 16:49:27 | 868.1 |  -91 |   7.8 |  -98.8 |    data | x_JSqA                                      
    2022-04-08 16:49:27 | 868.1 |  -93 |     7 | -100.0 |    data | kaj1Lg                                      
    2022-04-08 16:49:27 | 868.1 |  -93 |   8.8 | -101.8 |    data | 8r_cJQ                                      
    2022-04-08 16:49:29 | 868.1 | -119 |  -6.2 | -112.8 | witness | ycWNRqQ4-5qwSXR7YpAd-2lURKHm7n5D7eoWQvzLgbY
    2022-04-08 16:49:33 | 868.3 | -105 |   3.8 | -108.8 |    data | pvlm1g                                      
    2022-04-08 16:49:35 | 868.3 | -114 |    -7 | -107.0 |    data | iL-tQw                                      
    2022-04-08 16:49:37 | 868.3 | -110 |  -4.2 | -105.8 |    data | -_J6Cg                                      
    2022-04-08 16:49:40 | 868.5 | -108 |  -5.2 | -102.8 |    data | 88IFRQ                                      
    2022-04-08 16:49:43 | 868.5 | -103 |     1 | -104.0 |    data | Fvwvtw                                      
    2022-04-08 16:49:44 | 868.1 | -107 |     0 | -107.0 |    data | Lnywvg



### Export to CSV

    $ ./processlogs.php -cwitnesses.csv 
    Data saved to witnesses.csv


## To Do

* Retrieve Poc Receipt transaction from the blockchain for every witness  

