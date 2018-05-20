#!/bin/bash
kill -5 `cat pid.info` && php tcp-server.php
