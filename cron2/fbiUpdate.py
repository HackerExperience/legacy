import MySQLdb
import sys
import time

start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

cur.execute("	DELETE \
				FROM fbi \
				WHERE TIMESTAMPDIFF(SECOND, NOW(), dateEnd) < 0 \
			")

db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s"