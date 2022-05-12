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

    $ ./processlogs.php -l -s 2022-02-20 -e 2022-02-21 

    Using logs in /var/log/packet-forwarder/packet_forwarder.log

    Date                | RSSI | Freq  | SNR   | Noise  | Type    | Hash
    -------------------------------------------------------------------------------------------------------------
    02-05-2022 02:02:39 | 867.9 | -104 |     3 |   -107 | witness | eOLj-vzFUyYS8yCFw-UN8XV7ziY48rn2rOXXn5YtdAc
    02-05-2022 02:21:46 | 867.7 | -122 | -11.8 |   -110 | witness | QG6LVwQJrCNfHuj7auXZJ2G3k85GEACg7RPqorWIcGU
    02-05-2022 02:23:09 | 867.3 | -120 |  -9.8 |   -110 | witness | zGRhwbCmlZTOUz68MzSvqNsaeZB1LJXezpwBFSfxvS4
    02-05-2022 03:24:22 | 867.9 | -133 |   -17 |   -116 | witness | XsoOPQLWURiE5ABO8GQF7Tv455rmRmW31aZaL6gwAYY
    02-05-2022 03:24:24 | 868.1 |  -54 |   7.2 |    -61 | witness | k71Tky010RNUZRFPyWzv9Hl0TMs-aV3f7lEc1upuDlM
    02-05-2022 03:54:32 | 867.1 | -127 | -11.5 |   -116 | witness | Bg4WZsh5BpcREca86XNy2Un-01cFgYt1YBIlZtQNfs8
    02-05-2022 04:14:24 | 867.5 |   12 |       |        |  beacon | zw54wm5n1cfDDaTxvgVs5kbluXvAtE0PWa04XhITcd8
    02-05-2022 04:14:28 | 868.5 | -110 | -10.8 |    -99 | witness | zw54wm5n1cfDDaTxvgVs5kbluXvAtE0PWa04XhITcd8
    02-05-2022 04:16:20 | 868.1 | -119 |  -5.2 |   -114 | witness | hLI3I-C860X_HU0Iabnm-wzpScIqOPdxkqJ7ah5jv3w
    02-05-2022 04:26:22 | 867.9 |  -20 |     8 |    -28 | witness | JapFVXzV5TGzksRXITCE2BgbiZKOCqVjsiavxI3zN7o
    02-05-2022 04:41:44 | 867.3 | -120 |  -5.5 |   -115 | witness | c7S2HgxdbwfrB9ZuUO9pBeAlYhFXRspADpgKi0x-1Fw
    02-05-2022 05:07:17 | 867.1 | -117 |  -2.2 |   -115 | witness | 0zBHdkEozOXH_cY6Dlo7Gg7orhIacb7ppAlns3CdVVQ
    02-05-2022 05:40:54 | 867.3 | -103 |   4.5 |   -108 | witness | PrjYNiIi1_dcAQpT7i4bf2mHHJxslk36DhqTk9Mpzkc
    02-05-2022 05:51:48 | 867.5 | -110 |   3.5 |   -114 | witness | QM0XxmbdC7H9jum9jYWblZmxSad5ONtKXp7IKJVvoZ0
    02-05-2022 06:09:28 | 867.9 | -108 |   3.5 |   -112 | witness | 87yRNCuNYeT7_3qwwOZjY5P9N2SlWDlACirKmepXTnY
    02-05-2022 06:34:15 | 868.3 | -105 |     3 |   -108 | witness | TuqB36-xmFlpDlO-xYVXTvaZhkSgP3PmrdmE-sm6Ggc
    02-05-2022 07:05:33 | 867.5 |   12 |       |        |  beacon | WvFEb8dpVchq52FIJrfjrOq9VLGwRaaaURm4kZpK7E0
    02-05-2022 07:05:37 | 868.5 | -111 | -11.8 |    -99 | witness | WvFEb8dpVchq52FIJrfjrOq9VLGwRaaaURm4kZpK7E0
    02-05-2022 07:11:28 | 867.3 | -135 |   -21 |   -114 | witness | 5Z4bW8KVzHTlMKPIc9YXiO6towU5q_ZEK-xn6cn4yck
    02-05-2022 07:32:25 | 867.5 | -132 | -13.8 |   -118 | witness | ppRLQ76JRvBRdDO9Fru6CCiCtjoDZtTsY1XvMfo4h2Y
    02-05-2022 07:47:29 | 868.5 | -111 |  -4.5 |   -107 | witness | FD_wIdC2tIs5SamWA_JeMk0LkEVpu69zkRNvTtoYa3I
    02-05-2022 08:11:08 | 867.3 | -133 | -15.5 |   -118 | witness | oMZvUH1ciWhBHhPar_Ww3wzKHzQiKgsir-ergw441B8
    02-05-2022 08:39:42 | 867.3 | -137 | -21.2 |   -116 | witness | 5ux3EC3wYBM2MOAurgGyctNz-nC_WvWjg5YRRUk3ZB8
    02-05-2022 09:31:40 | 867.7 |  -55 |   8.5 |    -64 | witness | V44-ihwqsHq9JJvA9yoKQ1P1mSK9zSR7lnWDNvXSRDM
    02-05-2022 09:35:35 | 868.3 | -129 | -16.8 |   -112 | witness | zpdAH3Cm4h7tLlDNYURvqr5uw8DZ44Ze025MFkIZmdQ
    02-05-2022 10:00:16 | 868.3 | -122 |    -9 |   -113 | witness | FCM6r2kDA9neDs1X2gzceQSz59JjtFuUbpduTwNOw-o
    02-05-2022 10:11:25 | 868.3 | -115 |  -1.5 |   -114 | witness | rBw5Bkzshllw4F0gnvnp5W_JTnA8INGDy0mz0FGt_rk
    02-05-2022 10:41:21 | 867.3 | -128 | -18.8 |   -109 | witness | MTTtdxkZihMylvEZ-3B0I2lITVJPYNxg9ETBb52rHps
    02-05-2022 11:10:03 | 867.9 |  -55 |   8.2 |    -63 | witness | tqdE64tptZwyTjRzra3uuGALNrBLqB5ZjDghmuCNfyc
    02-05-2022 11:57:12 | 867.7 |  -56 |   7.8 |    -64 | witness | yUOWGy3ajJoNUKa6uFpFkuGCV3LClGYDId-DaF1SNmU
    02-05-2022 12:22:40 | 867.7 | -122 | -11.5 |   -111 | witness | h-LvjOMlWD8NUa_cDZ6RFfTsoTJw3ss8RbsUemLSY9Q
    02-05-2022 13:00:13 | 867.7 | -110 |  -0.5 |   -110 | witness | je08YOT65Wkt-zzAHvJbMCvAbmTWOLg5WNj799mj8Ak



## Show list of all packets received

    $ ./processlogs.php -ld 
    
    Using logs in /var/log/packet-forwarder/packet_forwarder.log
    
    Date                | Freq  | RSSI | SNR   | Noise  | Type    | Hash
    -------------------------------------------------------------------------------------------------------------
    02-05-2022 02:23:09 | 867.3 | -120 |  -9.8 |   -110 | witness | zGRhwbCmlZTOUz68MzSvqNsaeZB1LJXezpwBFSfxvS4
    02-05-2022 03:24:22 | 867.9 | -133 |   -17 |   -116 | witness | XsoOPQLWURiE5ABO8GQF7Tv455rmRmW31aZaL6gwAYY
    02-05-2022 03:24:24 | 868.1 |  -54 |   7.2 |    -61 | witness | k71Tky010RNUZRFPyWzv9Hl0TMs-aV3f7lEc1upuDlM
    02-05-2022 03:54:32 | 867.1 | -127 | -11.5 |   -116 | witness | Bg4WZsh5BpcREca86XNy2Un-01cFgYt1YBIlZtQNfs8
    02-05-2022 04:14:24 | 867.5 |   12 |       |        |  beacon | zw54wm5n1cfDDaTxvgVs5kbluXvAtE0PWa04XhITcd8
    02-05-2022 04:14:28 | 868.5 | -110 | -10.8 |    -99 | witness | zw54wm5n1cfDDaTxvgVs5kbluXvAtE0PWa04XhITcd8
    02-05-2022 04:16:20 | 868.1 | -119 |  -5.2 |   -114 | witness | hLI3I-C860X_HU0Iabnm-wzpScIqOPdxkqJ7ah5jv3w
    02-05-2022 04:26:22 | 867.9 |  -20 |     8 |    -28 | witness | JapFVXzV5TGzksRXITCE2BgbiZKOCqVjsiavxI3zN7o
    02-05-2022 04:41:44 | 867.3 | -120 |  -5.5 |   -115 | witness | c7S2HgxdbwfrB9ZuUO9pBeAlYhFXRspADpgKi0x-1Fw
    02-05-2022 05:07:17 | 867.1 | -117 |  -2.2 |   -115 | witness | 0zBHdkEozOXH_cY6Dlo7Gg7orhIacb7ppAlns3CdVVQ
    02-05-2022 05:40:54 | 867.3 | -103 |   4.5 |   -108 | witness | PrjYNiIi1_dcAQpT7i4bf2mHHJxslk36DhqTk9Mpzkc
    02-05-2022 05:51:48 | 867.5 | -110 |   3.5 |   -114 | witness | QM0XxmbdC7H9jum9jYWblZmxSad5ONtKXp7IKJVvoZ0
    02-05-2022 06:09:28 | 867.9 | -108 |   3.5 |   -112 | witness | 87yRNCuNYeT7_3qwwOZjY5P9N2SlWDlACirKmepXTnY
    02-05-2022 06:34:15 | 868.3 | -105 |     3 |   -108 | witness | TuqB36-xmFlpDlO-xYVXTvaZhkSgP3PmrdmE-sm6Ggc
    02-05-2022 07:05:33 | 867.5 |   12 |       |        |  beacon | WvFEb8dpVchq52FIJrfjrOq9VLGwRaaaURm4kZpK7E0
    02-05-2022 07:05:37 | 868.5 | -111 | -11.8 |    -99 | witness | WvFEb8dpVchq52FIJrfjrOq9VLGwRaaaURm4kZpK7E0
    02-05-2022 07:11:28 | 867.3 | -135 |   -21 |   -114 | witness | 5Z4bW8KVzHTlMKPIc9YXiO6towU5q_ZEK-xn6cn4yck
    02-05-2022 07:32:25 | 867.5 | -132 | -13.8 |   -118 | witness | ppRLQ76JRvBRdDO9Fru6CCiCtjoDZtTsY1XvMfo4h2Y
    02-05-2022 07:47:29 | 868.5 | -111 |  -4.5 |   -107 | witness | FD_wIdC2tIs5SamWA_JeMk0LkEVpu69zkRNvTtoYa3I
    02-05-2022 08:11:08 | 867.3 | -133 | -15.5 |   -118 | witness | oMZvUH1ciWhBHhPar_Ww3wzKHzQiKgsir-ergw441B8
    02-05-2022 08:39:42 | 867.3 | -137 | -21.2 |   -116 | witness | 5ux3EC3wYBM2MOAurgGyctNz-nC_WvWjg5YRRUk3ZB8
    02-05-2022 09:31:40 | 867.7 |  -55 |   8.5 |    -64 | witness | V44-ihwqsHq9JJvA9yoKQ1P1mSK9zSR7lnWDNvXSRDM
    02-05-2022 09:35:35 | 868.3 | -129 | -16.8 |   -112 | witness | zpdAH3Cm4h7tLlDNYURvqr5uw8DZ44Ze025MFkIZmdQ
    02-05-2022 10:00:16 | 868.3 | -122 |    -9 |   -113 | witness | FCM6r2kDA9neDs1X2gzceQSz59JjtFuUbpduTwNOw-o
    02-05-2022 10:11:25 | 868.3 | -115 |  -1.5 |   -114 | witness | rBw5Bkzshllw4F0gnvnp5W_JTnA8INGDy0mz0FGt_rk
    02-05-2022 10:41:21 | 867.3 | -128 | -18.8 |   -109 | witness | MTTtdxkZihMylvEZ-3B0I2lITVJPYNxg9ETBb52rHps




### Export to CSV

    $ ./processlogs.php -cwitnesses.csv 
    Data saved to witnesses.csv


## To Do

* Retrieve Poc Receipt transaction from the blockchain for every witness  

