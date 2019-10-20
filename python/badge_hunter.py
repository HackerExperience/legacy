import MySQLdb
import time

start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

def adiciona_badge(badgeID, userID, clanBadge = False):

	import badge_add

	if not clanBadge:
		userBadge = 'user'
	else:
		userBadge = 'clan'

	userID = int(userID)

	badge_add.userBadge = userBadge
	badge_add.userID = userID
	badge_add.badgeID = badgeID

	badge_add.badge_add()

if __name__ == "__main__":

	#badge 'h4x0r' (+ de 100 ips na lista)
	cur.execute('	SELECT COUNT(*) AS total, userID \
					FROM lists \
					GROUP BY userID \
					ORDER BY total DESC \
				')

	for totalList, userID in cur.fetchall():
		if totalList < 100:
			break

		adiciona_badge(22, userID)

	#badge 'b4nk3r' (+ de 50 accs na lista)	
	cur.execute('	SELECT COUNT(*) AS total, userID \
					FROM lists_bankAccounts \
					GROUP BY userID \
					ORDER BY total DESC \
				')

	for totalList, userID in cur.fetchall():
		if totalList < 50:
			break

		adiciona_badge(23, userID)

	#badge 'who ate my ram' (20+ running softwares)
	cur.execute('	SELECT COUNT(*) AS total, userID \
					FROM software_running \
					WHERE isNPC = 0 \
					GROUP BY userID \
					ORDER BY total DESC \
				')

	for totalRunning, userID in cur.fetchall():
		if totalRunning < 20:
			break

		adiciona_badge(51, userID)

	#badge 'Employee' (50+ completed missions)
	cur.execute('	SELECT COUNT(*) AS total, userID \
					FROM missions_history \
					WHERE completed = 1 \
					GROUP BY userID \
					ORDER BY total DESC \
				')

	for totalCompleted, userID in cur.fetchall():
		if totalCompleted < 50:
			break

		adiciona_badge(36, userID)

	#badge 'I Cant Handle' (20+ ip resets)
	cur.execute('	SELECT uid \
					FROM users_stats \
					WHERE ipResets >= 20 \
				')

	for userID in cur.fetchall():
		adiciona_badge(38, userID[0])

	#badge 'Addicted player' (24h+ game play)
	cur.execute('	SELECT uid \
					FROM users_stats \
					WHERE timePlaying >= 86400 \
				')

	for userID in cur.fetchall():
		adiciona_badge(40, userID[0])

	#badge 'Rich' ($1,000,000+ on all bank accounts)
	cur.execute('	SELECT SUM(cash) AS total, bankUser \
					FROM bankAccounts \
					GROUP BY bankUser \
					ORDER BY total DESC \
				')

	for totalMoney, userID in cur.fetchall():
		if totalMoney < 1000000:
			break

		adiciona_badge(55, userID)

	#badge 'DDoSer' (100+ ddos attacks)
	cur.execute('	SELECT uid \
					FROM users_stats \
					WHERE ddosCount >= 100 \
				')

	for userID in cur.fetchall():
		adiciona_badge(56, userID[0])

	#badge 'Efficient' (depois de 10 missoes, ter uma rate de 95%)
	cur.execute('	SELECT \
					userID AS curUser, \
					COUNT(*) AS total, \
					ROUND((COUNT(*) / (SELECT COUNT(*) FROM missions_history WHERE userID = curUser))*100) AS rate \
					FROM missions_history \
					WHERE completed = 1 \
					GROUP BY userID \
					ORDER BY \
						total DESC, \
						rate DESC \
				')

	for userID, totalMissions, rate in cur.fetchall():
		if totalMissions < 10:
			break

		if rate < 95:
			continue

		adiciona_badge(59, userID)		

	#badge 'researcher' (+ de 50 researchs no round)
	cur.execute('''	SELECT userID, COUNT(*) AS total
					FROM software_research
					GROUP BY userID
					ORDER BY total DESC
				''')

	for userID, totalResearch in cur.fetchall():
		if totalResearch < 50:
			break

		adiciona_badge(65, userID)

	#badge 'Hacker' (100+ hack count)
	cur.execute('	SELECT uid \
					FROM users_stats \
					WHERE hackCount >= 100 \
				')

	for userID in cur.fetchall():
		adiciona_badge(67, userID[0])

	#badge 'What'ya Doin' (50+ running tasks at once)
	cur.execute('	SELECT COUNT(*) AS total, pCreatorID \
					FROM processes \
					WHERE \
						TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) < 0 AND \
						isPaused = 0 \
					GROUP BY pCreatorID \
					ORDER BY total DESC \
				')

	for totalRunning, userID in cur.fetchall():
		if totalRunning < 50:
			break

		adiciona_badge(69, userID)

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s\n"