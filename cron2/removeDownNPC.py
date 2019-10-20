import MySQLdb
import sys
import time

start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

cur.execute("	DELETE \
				FROM npc_down \
				WHERE TIMESTAMPDIFF(SECOND, NOW(), downUntil) < 0 \
			")

db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s"