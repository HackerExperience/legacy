#!/bin/sh

/usr/bin/env python /var/www/cron2/updateCurStats.py; 
/usr/bin/env python /var/www/cron2/updateRanking.py; 
/usr/bin/env python /var/www/python/rank_generator.py;
