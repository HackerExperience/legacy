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

	#badge 'Web Celeb' (profile visited 1000+ times)
	cur.execute('''	SELECT 
						hist_users.userID,
						(
							SUM(hist_users.profileviews) + 
							SUM(hist_users_current.profileviews)
						) AS totalClicks
					FROM hist_users
					INNER JOIN hist_users_current
					ON hist_users_current.userID = hist_users.userID
					GROUP BY hist_users.userID
					ORDER BY totalClicks DESC
				''')

	for userID, totalClicks in cur.fetchall():

		if totalClicks < 1000:
			break

		adiciona_badge(31, userID)

	#badge 'you are addicted' (complete a total of 500 missions)
	cur.execute('''	SELECT 
						hist_missions.userID, 
						COUNT(*) + 
						(
							SELECT COUNT(*)
							FROM missions_history
							WHERE 
								missions_history.userID = hist_missions.userID AND
								completed = 1
						) AS totalMissions
					FROM hist_missions
					WHERE completed = 1
					GROUP BY userID
					ORDER BY totalMissions DESC
				''')

	for userID, totalResets in cur.fetchall():

		if totalResets < 500:
			break

		adiciona_badge(37, userID)

	#badge 'Noob Certification' (reset ips over 100 times)
	cur.execute('''	SELECT 
						hist_users.userID,
						(
							SUM(hist_users.ipresets) + 
							SUM(hist_users_current.ipresets)
						) AS totalResets
					FROM hist_users
					INNER JOIN hist_users_current
					ON hist_users_current.userID = hist_users.userID
					GROUP BY hist_users.userID
					ORDER BY totalResets DESC
				''')

	for userID, totalResets in cur.fetchall():

		if totalResets < 100:
			break

		adiciona_badge(39, userID)

	#badge 'I need help' (timeplaying >= 14 dias)
	cur.execute('''	SELECT 
						hist_users.userID,
						(
							SUM(hist_users.timeplaying) + 
							SUM(hist_users_current.timeplaying)
						) AS totalPlaying
					FROM hist_users
					INNER JOIN hist_users_current
					ON hist_users_current.userID = hist_users.userID
					GROUP BY hist_users.userID
					ORDER BY totalPlaying DESC
				''')

	for userID, totalPlaying in cur.fetchall():

		if totalPlaying < 20160:
			break

		adiciona_badge(41, userID)
		

	#badges de 1, 2 e 5 anos de idade
	cur.execute('	SELECT uid, TIMESTAMPDIFF(YEAR, dateJoined, NOW()) > 0 AS age \
					FROM users_stats \
					WHERE TIMESTAMPDIFF(YEAR, dateJoined, NOW()) > 0 \
				')

	for userID, age in cur.fetchall():
		if age == 1:
			adiciona_badge(42, userID)
			continue

		if age == 2:
			adiciona_badge(43, userID)
			continue

		if age == 5:
			adiciona_badge(44, userID)
			continue

	#badges 'I haz fame' e 'Powerful member' (reputation over 1kk or 10kk)
	cur.execute('''	SELECT 
						hist_users.userID,
						(
							SUM(hist_users.reputation) + 
							SUM(hist_users_current.reputation)
						) AS totalRep
					FROM hist_users
					INNER JOIN hist_users_current
					ON hist_users_current.userID = hist_users.userID
					GROUP BY hist_users.userID
					ORDER BY totalRep DESC
				''')

	for userID, totalRep in cur.fetchall():

		if totalRep < 1000000:
			break

		if totalRep < 10000000:
			adiciona_badge(46, userID)
		else:
			adiciona_badge(47, userID)
		
	#badge 'DDoS Master' (ddoscount over 1000)
	cur.execute('''	SELECT 
						hist_users.userID,
						(
							SUM(hist_users.ddoscount) + 
							SUM(hist_users_current.ddoscount)
						) AS totalDdos
					FROM hist_users
					INNER JOIN hist_users_current
					ON hist_users_current.userID = hist_users.userID
					GROUP BY hist_users.userID
					ORDER BY totalDdos DESC
				''')

	for userID, totalDDoS in cur.fetchall():
		if totalDDoS < 1000:
			break

		adiciona_badge(57, userID)

	#badge 'Talker' (send over 100 emails)
	cur.execute('''	SELECT 
							mails.from,
							COUNT(*) + 
							(
								SELECT COUNT(*)
								FROM hist_mails
								WHERE hist_mails.from = mails.from
							) AS total
					FROM mails
					WHERE 
						mails.to > 0 AND 
						mails.from > 0
					GROUP BY mails.from
					ORDER BY total DESC
				''')

	for userID, totalSent in cur.fetchall():
		if totalSent < 100:
			break

		adiciona_badge(63, userID)

	#badge 'Famous' (receive over 50 emails)
	cur.execute('''	SELECT 
							mails.to,
							COUNT(*) + 
							(
								SELECT COUNT(*)
								FROM hist_mails
								WHERE hist_mails.to = mails.to
							) AS total
					FROM mails
					WHERE 
						mails.to > 0 AND 
						mails.from > 0
					GROUP BY mails.to
					ORDER BY total DESC
				''')

	for userID, totalReceived in cur.fetchall():
		if totalReceived < 50:
			break

		adiciona_badge(64, userID)

	#badge 'software engineer' (researchcount over 500 )
	cur.execute('''	SELECT 
						hist_users.userID,
						(
							SUM(researchCount) + 
							(
								SELECT COUNT(*)
								FROM software_research
								WHERE software_research.userID = hist_users.userID
								GROUP BY software_research.userID
							)
						) AS totalResearch
					FROM hist_users
					GROUP BY hist_users.userID
					ORDER BY totalResearch DESC
				''')

	for userID, totalResearch in cur.fetchall():
		if totalResearch < 500:
			break

		adiciona_badge(66, userID)

	#badge 'hacker master' (hackcount over 1000)
	cur.execute('''	SELECT 
						hist_users.userID,
						(
							SUM(hist_users.hackCount) + 
							SUM(hist_users_current.hackCount)
						) AS totalHack
					FROM hist_users
					INNER JOIN hist_users_current
					ON hist_users_current.userID = hist_users.userID
					GROUP BY hist_users.userID
					ORDER BY totalHack DESC
				''')

	for userID, totalHack in cur.fetchall():
		if totalHack < 1000:
			break

		adiciona_badge(68, userID)


print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s\n"