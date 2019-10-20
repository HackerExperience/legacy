import MySQLdb
import sys
import time

start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

cur.execute("	SELECT id, boughtDate, premiumUntil, totalPaid \
				FROM users_premium \
				WHERE TIMESTAMPDIFF(SECOND, NOW(), premiumUntil) < 0 \
			")

imported = False

for userID, bought, premium, paid in cur.fetchall():

	if not imported:

		imported = True
		sys.path.insert(0, '/var/www/python')
		import badge_add

	badge_add.userBadge = 'user'
	badge_add.userID = userID
	badge_add.badgeID = 80 #Donator badge

	badge_add.badge_add()

	cur.execute("INSERT INTO premium_history \
					(userID, boughtDate, premiumUntil, paid)\
				VALUES \
					(%s, %s, %s, %s)\
				", (userID, bought, premium, paid))

	cur.execute("	DELETE FROM users_premium \
					WHERE id = %s AND premiumUntil = %s \
				", (userID, premium))

	cur.execute("	UPDATE internet_webserver \
					SET active = 0 \
					WHERE id = %s \
				", userID)

db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s"