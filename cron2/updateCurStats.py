import MySQLdb
import time
start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

cur.execute("""	SELECT 
					h.userID, s.exp, s.dateJoined, s.timePlaying, s.bitcoinSent, s.warezSent, s.spamSent, 
					s.profileViews, s.hackCount, s.ipResets, s.moneyEarned, s.moneyTransfered, s.moneyHardware, s.moneyResearch,
					clan.clanID, clan.name
				FROM hist_users_current h
				INNER JOIN users_stats s ON s.uid = h.userID
 				LEFT JOIN clan_users ON clan_users.userID = h.userID
 				LEFT JOIN clan ON clan.clanID = clan_users.clanID
			""")
rank = 0
for userID, exp, dateJoined, timePlaying, bitcoinSent, warezSent, spamSent, profileViews, hackCount, ipResets, moneyEarned, moneyTransfered, moneyHardware, moneyResearch, cid, cname in cur.fetchall():

	cur.execute("""	SELECT COUNT(*)
					FROM round_ddos
					WHERE attID = """+str(userID)+"""
					""")

	for ddos in cur.fetchall():

		if ddos[0] > 0:
			ddos = ddos[0]
		else:
			ddos = 0

		cur.execute("""	UPDATE hist_users_current 
						SET 
							reputation = %s,
							age = TIMESTAMPDIFF(DAY, %s, NOW()),
							clanID = %s,
							clanName = %s,
							timePlaying = %s,
							warezSent = %s,
							spamSent = %s,
							bitcoinSent = %s,
							profileViews = %s,
							hackCount = %s,
							ddosCount = %s,
							ipResets = %s,
							moneyEarned = %s,
							moneyTransfered = %s,
							moneyHardware = %s,
							moneyResearch = %s
						WHERE userID = %s
					""", (exp, dateJoined, cid, cname, timePlaying, bitcoinSent, warezSent, spamSent, profileViews, hackCount, ddos, ipResets, moneyEarned, moneyTransfered, moneyHardware, moneyResearch, userID))

	cur.execute("""	UPDATE cache 
					SET cache.reputation = %s 
					WHERE userID = %s 
				""", (exp, userID))

#db.commit()

# import sys
# sys.exit()

# cur.execute("""	SELECT 
# 				FROM clan
# 				INNER JOIN users_stats s ON s.uid = h.userID
#  				LEFT JOIN clan_users ON clan_users.userID = h.userID
#  				LEFT JOIN clan ON clan.clanID = clan_users.clanID
# 			""")

cur.execute("	UPDATE hist_clans_current \
				INNER JOIN clan_stats ON clan_stats.cid = hist_clans_current.cid\
				SET \
					hist_clans_current.clanIP = ( \
						SELECT npcIP \
						FROM npc \
						WHERE npc.id = hist_clans_current.cid \
					), \
					hist_clans_current.reputation = ( \
						SELECT power \
						FROM clan \
						WHERE clan.clanID = hist_clans_current.cid\
					), \
					hist_clans_current.members = ( \
						SELECT COUNT(*) \
						FROM clan_users \
						WHERE clan_users.clanID = hist_clans_current.cid\
					), \
					hist_clans_current.won = clan_stats.won, \
					hist_clans_current.lost = clan_stats.lost, \
					hist_clans_current.clicks = clan_stats.pageClicks \
			")

db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s\n"