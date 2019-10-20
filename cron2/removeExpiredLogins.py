import MySQLdb
import time
start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

cur.execute("""	DELETE users_expire, users_online, internet_connections
				FROM users_expire
				LEFT JOIN users_online
				ON users_online.id = users_expire.userID
				LEFT JOIN internet_connections
				ON internet_connections.userID = users_expire.userID
				WHERE 
					TIMESTAMPDIFF(SECOND, expireDate, NOW()) > 0
			""")

cur.execute("""	DELETE users_online, internet_connections
				FROM users_online
				LEFT JOIN internet_connections
				ON internet_connections.userID = users_online.id
				WHERE 
					TIMESTAMPDIFF(HOUR, loginTime, NOW()) > 10
			""")

db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s \n"