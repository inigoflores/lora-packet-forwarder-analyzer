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

    sudo ./install.sh

Packet logging will start straight away in /var/log/packet-forwarder/packet_forwarder.log  

You can uninstall the service by running:

    sudo ./uninstall.sh

## Tool usage

    $ ./processlogs.php { -a | -l | -i } [-s YYYY-MM-DD] [-e YYYY-MM-DD] [-d] [-p /FULL/PATH/TO/LOGS] [-c[FILENAME.CSV]]


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

    ./processlogs.php

    Using logs in /var/log/packet-forwarder/packet_forwarder.log
    
    First Packet:        01-05-2022 16:55:14
    Last Packet:         02-05-2022 22:16:18 (29.4 hours)
    
    Total Witnesses:           62     (2.11/hour)
    Total Packets:          10539   (359.07/hour)
    Total Beacons:              3     (2.45/day)
    Lowest Witness RSSI:     -138 dBm
    Lowest Packet RSSI:      -140 dBm
    
          -----------------------------------------------------------------------------  
          |        Witnesses                    |          All Packets              
          -----------------------------------------------------------------------------  
    Freq  | Num  | RSSI Avg | RSSI Min | SNR    | Num    | RSSI Avg | RSSI Min | SNR
    -----------------------------------------------------------------------------------  
    867.1 |    3 |  -127.33 |  -138.00 | -12.23 |     25 |  -131.16 |  -139.00 | -16.90
    867.3 |   10 |  -122.50 |  -137.00 | -10.39 |     36 |  -128.36 |  -140.00 | -14.94
    867.5 |    8 |  -115.88 |  -132.00 |  -4.91 |     21 |  -123.48 |  -135.00 | -11.77
    867.7 |   12 |  -111.58 |  -135.00 |  -7.19 |     36 |  -123.97 |  -138.00 | -13.01
    867.9 |   11 |  -105.45 |  -134.00 |  -5.24 |     38 |  -124.16 |  -138.00 | -13.99
    868.1 |    5 |  -109.60 |  -131.00 |  -7.10 |   6519 |  -115.87 |  -137.00 |  -2.64
    868.3 |   10 |  -114.30 |  -131.00 |  -7.23 |   3718 |  -119.28 |  -136.00 |  -7.15
    868.5 |    3 |  -110.67 |  -111.00 |  -9.03 |      8 |  -116.25 |  -121.00 |  -9.46
    ------------------------------------------------------------------------------------  




### Show list of all witnesses between two dates

    $ ./processlogs.php -l -s 2022-05-24 -e 2022-05-25 

    Using logs in /var/log/packet-forwarder/packet_forwarder.log

    Date                | Freq  | RSSI | SNR   | Noise  | Type    | Datarate | Hash
    -------------------------------------------------------------------------------------------------------------
    24-05-2022 14:08:29 | 868.3 | -120 |  -6.8 |   -113 | witness | SF12BW125 | zW6GwIgWG2IEgaFAhnDYvYpveHAJAHckEI_yH9yuLwY
    24-05-2022 14:10:28 | 868.3 |  -55 |     7 |    -62 | witness | SF12BW125 | n8GRNB71UElgsbJhNuifBQE4xYVwl99dDNc_GeAvAbA
    24-05-2022 16:20:34 | 867.9 | -119 |  -4.5 |   -115 | witness | SF12BW125 | RqM4glRHg15BBI2aW_bmm6DlooTY2d0SRxUxDF81qy0
    24-05-2022 16:57:00 | 868.1 | -139 | -25.2 |   -114 | witness | SF12BW125 | PB4mrUZYHQZQoyDCKNU6pMT4I4Va86zLT0mfgVlLo6k
    24-05-2022 17:17:52 | 867.1 | -137 | -21.8 |   -115 | witness | SF12BW125 | TEOhgaFaRhTYVnhZFXGyM6RLNharZvSWpJMbrEkTRDw
    24-05-2022 18:15:25 | 868.3 | -121 | -13.2 |   -108 | witness | SF12BW125 | 46GCk-z8c7ppgDVAk8kJpyXCRtY4ezHaFyjPsD9h_b8
    24-05-2022 19:14:00 | 867.7 | -127 | -15.8 |   -111 | witness | SF12BW125 | hh0Uh1Xyguqw2_8WGpkyBkKM1inUJCTWxbnIyTbg0i0
    24-05-2022 22:21:10 | 867.1 | -127 | -16.8 |   -110 | witness | SF12BW125 | Gj2tLlx4Rb-GJ5dpAIezTOtdCNQV3L1Bfchx93uFpEs
    24-05-2022 22:51:35 | 867.7 | -128 | -17.8 |   -110 | witness | SF12BW125 | 5OefFeSnx9Fmqs8QW2EDiC5n_CEweDR0Wxe8FFgHRHM
    25-05-2022 01:00:45 | 867.7 | -121 |  -6.8 |   -114 | witness | SF12BW125 | r9IVtYqsYLrSDcrfcrxIs-SRQTm14tGvmRbJA1KhHUQ
    25-05-2022 01:14:12 | 868.3 | -133 | -19.2 |   -114 | witness | SF12BW125 | 7YuUPQf6puJe2tO0y4pe4bTRCT7PIlTXhlppl2ZCObM
    25-05-2022 02:25:05 | 868.5 | -114 | -17.5 |    -97 | witness | SF12BW125 | SoqlOSO1VgoZjL7qV0aWMmvkb9vTioa7-pSUeZskLb4
    25-05-2022 02:35:45 | 867.7 | -124 |    -9 |   -115 | witness | SF12BW125 | 5iNlX-C5d-v4kWIHOntUALeMhkZhxG5KhWowepkaDT0
    25-05-2022 03:43:19 | 867.5 | -129 | -20.2 |   -109 | witness | SF12BW125 | IgqnPD9f2OAaOCdd6cElRgmwPf6TuoPuaRrKypoIikI
    25-05-2022 03:48:57 | 867.7 | -136 | -20.8 |   -115 | witness | SF12BW125 | Msq_AdITPadF8jo6mCHuDslLk7wfFnQ3p8yAhGzb0K0
    25-05-2022 03:54:45 | 867.1 | -120 |  -4.5 |   -116 | witness | SF12BW125 | uogp3F9RwV0Jpku2crJ6ZxaqBjCH1PlL6iGMtaqKkXc
    25-05-2022 04:25:47 | 867.9 |  -98 |   5.5 |   -104 | witness | SF12BW125 | I8r9zjDADlbHIGuq-KyrHNqfIZTQ3v2I6NJweEWPE1w
    25-05-2022 04:33:37 | 867.7 | -132 |   -30 |   -102 | witness | SF12BW125 | ZpKKPAaQp1Itcn7FXV7uNcVcbrqy-qo9mXFyTR6raqE
    25-05-2022 04:58:42 | 868.3 | -105 |   2.5 |   -108 | witness | SF12BW125 | qKCIVBIQp4AsY4STD1_yv3eGOPYqqb_XOUEEnYPg0dg
    25-05-2022 06:13:32 | 867.3 | -136 |   -22 |   -114 | witness | SF12BW125 | DB8uNX6Z7o3Va3A2TD9TlySyAGPwXcdmoDQDACGfDy4
    25-05-2022 06:15:50 | 867.1 | -126 | -10.8 |   -115 | witness | SF12BW125 | -P0VK9TX5CuuyCG2kAw_YTKbxY8ZMXVLgScp3pZPVvs
    25-05-2022 06:39:57 | 867.3 | -122 |    -9 |   -113 | witness | SF12BW125 | eSKft63XiPkMsyCwaot6tUzYSX_Uk8wN-bkwoMjwad0
    25-05-2022 07:17:38 | 867.1 | -135 | -19.8 |   -115 | witness | SF12BW125 | xeObtMYgYx8kZ8EZq_POvTojZSlujQEW5tfdMyhatBE
    25-05-2022 07:25:06 | 867.1 | -108 |   3.5 |   -112 | witness | SF12BW125 | VBKEVOlEQrj5VjTApLLOeuIy42oBz9af_rkNEQE58wo
    25-05-2022 07:25:38 | 867.7 | -129 | -14.2 |   -115 | witness | SF12BW125 | tbQdD02FxwFksds8zDirlJ3qyaykIcr6tmI_DYUgpsE




## Show list of all packets received

    $ ./processlogs.php -l -d 
    
    Using logs in /var/log/packet-forwarder/packet_forwarder.log
    
    Date                | Freq  | RSSI | SNR   | Noise  | Type    | Datarate  | Hash
    -------------------------------------------------------------------------------------------------------------
    25-05-2022 13:55:34 | 868.1 | -104 |   3.2 |   -107 | rx data |  SF7BW125 | Nxjiqw                                      
    25-05-2022 13:55:36 | 868.1 | -105 |   3.8 |   -109 | rx data |  SF7BW125 | Bu0uqA                                      
    25-05-2022 13:55:37 | 868.1 | -105 |   3.2 |   -108 | rx data |  SF7BW125 | Bu0uqA                                      
    25-05-2022 13:55:37 | 868.1 | -105 |   3.5 |   -109 | rx data |  SF7BW125 | ULeJLg                                      
    25-05-2022 13:55:37 | 868.1 | -105 |     3 |   -108 | rx data |  SF7BW125 | 8n7DWQ                                      
    25-05-2022 13:56:32 | 868.1 | -105 |   2.5 |   -108 | rx data |  SF7BW125 | N9n91w                                      
    25-05-2022 13:56:32 | 868.1 | -105 |   1.8 |   -107 | rx data |  SF7BW125 | G-geEA                                      
    25-05-2022 13:56:32 | 868.1 | -105 |   2.2 |   -107 | rx data |  SF7BW125 | TbK5lg                                      
    25-05-2022 13:56:32 | 868.1 | -104 |   1.8 |   -106 | rx data |  SF7BW125 | G-geEA                                      
    25-05-2022 13:56:56 | 867.9 | -106 |  -0.5 |   -106 | witness | SF12BW125 | 9q5J66bC3AAziwi7NQxSJazU1nSaT9ntwyA03S4oOis
    25-05-2022 13:56:59 | 868.1 | -105 |   3.2 |   -108 | rx data |  SF7BW125 | x_JSqA                                      
    25-05-2022 13:56:59 | 868.1 | -105 |   2.8 |   -108 | rx data |  SF7BW125 | x_JSqA                                      
    25-05-2022 13:57:00 | 868.1 | -105 |   3.5 |   -109 | rx data |  SF7BW125 | kaj1Lg                                      
    25-05-2022 13:57:00 | 868.1 | -105 |   2.5 |   -108 | rx data |  SF7BW125 | 8r_cJQ                                      
    25-05-2022 13:57:37 | 868.1 | -105 |   2.2 |   -107 | rx data |  SF7BW125 | dPd6Vg                                      
    25-05-2022 13:57:38 | 868.1 | -105 |   2.2 |   -107 | rx data |  SF7BW125 | dPd6Vg                                      
    25-05-2022 13:57:38 | 868.1 | -105 |   2.8 |   -108 | rx data |  SF7BW125 | dPd6Vg                                      
    25-05-2022 13:57:48 | 868.1 | -104 |   2.5 |   -107 | rx data |  SF7BW125 | dPd6Vg                                      
    25-05-2022 13:57:48 | 868.1 | -105 |   2.5 |   -108 | rx data |  SF7BW125 | Iq3d0A                                      
    25-05-2022 13:57:48 | 868.1 | -104 |   2.8 |   -107 | rx data |  SF7BW125 | dPd6Vg                                      
    25-05-2022 13:57:49 | 868.1 | -104 |   3.2 |   -107 | rx data |  SF7BW125 | qAkGFg                                      
    25-05-2022 13:58:01 | 868.1 | -117 |  -9.8 |   -107 | rx data |  SF7BW125 | 8D150w                                      
    25-05-2022 13:58:01 | 868.1 | -119 |   -11 |   -108 | rx data |  SF7BW125 | AjBdFw                                      
    25-05-2022 13:58:34 | 868.1 | -105 |     3 |   -108 | rx data |  SF7BW125 | jK3Flg                                      
    25-05-2022 13:58:34 | 868.1 | -105 |   3.2 |   -108 | rx data |  SF7BW125 | Nxjiqw                                      
    25-05-2022 13:58:34 | 868.1 | -105 |     3 |   -108 | rx data |  SF7BW125 | 2vdiEA                                      
    25-05-2022 13:58:34 | 868.1 | -105 |     3 |   -108 | rx data |  SF7BW125 | 2vdiEA                                      
    25-05-2022 13:58:41 | 868.1 | -105 |   1.2 |   -106 | rx data |  SF7BW125 | Bu0uqA                                      
    25-05-2022 13:58:41 | 868.1 | -105 |     1 |   -106 | rx data |  SF7BW125 | Bu0uqA


### Print a command-line ASCII histogram of all packets received

    $ ./processlogs.php -i -d

    Using logs in /var/log/packet-forwarder/packet_forwarder.log

    30-04-2022 16:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1327
    30-04-2022 17:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1469
    30-04-2022 18:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1343
    30-04-2022 19:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1757
    30-04-2022 20:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1532
    30-04-2022 21:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1063
    30-04-2022 22:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1629
    30-04-2022 23:00 ■■■■■■■■■■■■■■■■■■■■■■■■■ 669
    01-05-2022 00:00 ■■■■■■■■■■■■■■■■■■ 491
    01-05-2022 01:00 ■■■■■■■■■■■■■■■■ 431
    01-05-2022 02:00 ■■■■■■■■■■■■■■■ 405
    01-05-2022 03:00 ■■■■■■■■■■■■■■■■■■■■ 548
    01-05-2022 04:00 ■■■■■■■■■■■■■■ 376
    01-05-2022 05:00 ■■■■■■■■■■■■ 328
    01-05-2022 06:00 ■■■■■■■■■■■■■■ 380
    01-05-2022 07:00 ■■■■■■■■■■■■■■■■■ 445
    01-05-2022 08:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1139
    01-05-2022 09:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1849
    01-05-2022 10:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1197
    01-05-2022 11:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1241
    01-05-2022 12:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1747
    01-05-2022 13:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1564
    01-05-2022 14:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1994
    01-05-2022 15:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1839
    01-05-2022 16:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1868
    01-05-2022 17:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1234
    01-05-2022 18:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1755
    01-05-2022 19:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 2045
    01-05-2022 20:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1538
    01-05-2022 21:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1591
    01-05-2022 22:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1754
    01-05-2022 23:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1300
    02-05-2022 00:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 801
    02-05-2022 01:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 827
    02-05-2022 02:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1193
    02-05-2022 03:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1013
    02-05-2022 04:00 ■■■■■■■■■■■■■■■■■■■■■■■■ 650
    02-05-2022 05:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■ 726
    02-05-2022 06:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 949
    02-05-2022 07:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1322
    02-05-2022 08:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1642
    02-05-2022 09:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 2198
    02-05-2022 10:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1607
    02-05-2022 11:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1602
    02-05-2022 12:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1713
    02-05-2022 13:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1842
    02-05-2022 14:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1700
    02-05-2022 15:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1808
    02-05-2022 16:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1849
    02-05-2022 17:00 ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ 1678

### Export histogram in CSV

    ./processlogs.php -i -d -cpackets.csv
    
    Using logs in /var/log/packet-forwarder/packet_forwarder.log
    
    Data saved to packets.csv

The following chart was created with Excel:

![lora-packets](https://user-images.githubusercontent.com/5518087/168495187-e280672a-dd66-4934-9b23-ccd6eef2bb78.png)

### Export to CSV

    $ ./processlogs.php -l -cwitnesses.csv 
    Data saved to witnesses.csv


## To Do

* Retrieve Poc Receipt transaction from the blockchain for every witness  

