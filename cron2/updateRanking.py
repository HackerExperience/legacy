import MySQLdb
import time
start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

#user ranking

# cur.execute("	UPDATE ranking_user \
# 				INNER JOIN ( \
# 					SELECT h.userID, COUNT(i.userID) AS total \
# 					FROM hist_users_current h \
# 					LEFT JOIN hist_users_current i ON i.reputation > h.reputation \
# 					GROUP BY h.userID \
# 				) r ON r.userID = ranking_user.userID \
# 				SET rank = r.total + 1 \
# 			")

cur.execute(" SELECT userID FROM hist_users_current WHERE reputation > 1000 ORDER BY reputation DESC")
rank = 0
for userID in cur.fetchall():

	rank += 1
	userID = userID[0]

	cur.execute("UPDATE ranking_user SET rank = "+str(rank)+" WHERE userID = "+str(userID))


#Clan ranking

# cur.execute("	UPDATE ranking_clan \
# 				INNER JOIN ( \
# 					SELECT h.cid, COUNT(i.cid) AS total \
# 					FROM hist_clans_current h \
# 					LEFT JOIN hist_clans_current i ON i.reputation > h.reputation \
# 					GROUP BY h.cid \
# 				) r ON r.cid = ranking_clan.clanID \
# 				SET rank = r.total + 1\
# 			")

cur.execute(" SELECT cid FROM hist_clans_current WHERE reputation > 0 ORDER BY reputation DESC")
rank = 0
for clanID in cur.fetchall():

	rank += 1

	clanID = clanID[0]

	cur.execute("UPDATE ranking_clan SET rank = "+str(rank)+" WHERE clanID = "+str(clanID))


# cur.execute("""	UPDATE ranking_software 
# 				INNER JOIN ( 
# 					SELECT s.softID, COUNT(i.softID) AS total 
# 					FROM software_research s 
# 					LEFT JOIN software_research i 
# 					ON 
# 						i.newVersion > s.newVersion AND 
# 						i.softID IN ( 
# 							SELECT softID 
# 							FROM ranking_software 
# 						)
# 					WHERE
# 						s.softID IN ( 
# 							SELECT softID 
# 							FROM ranking_software 
# 						)
# 					GROUP BY s.softID
# 				) r ON r.softID = ranking_software.softID 
# 				SET rank = r.total + 1
# 			""")

cur.execute("SELECT softID FROM software_research WHERE softID IN ( SELECT softID FROM ranking_software ) ORDER BY newVersion DESC")
rank = 0
for softID in cur.fetchall():

	rank += 1

	softID = softID[0]

	cur.execute("UPDATE ranking_software SET rank = "+str(rank)+" WHERE softID = "+str(softID))


# cur.execute("	UPDATE ranking_ddos \
# 				INNER JOIN ( \
# 					SELECT d.id, COUNT(i.id) AS total \
# 					FROM round_ddos d \
# 					LEFT JOIN round_ddos i ON i.power > d.power AND i.vicNPC = 0 \
# 					WHERE d.vicNPC = 0 \
# 					GROUP BY d.id \
# 				) r ON r.id = ranking_ddos.ddosID \
# 				SET rank = r.total + 1\
# 			")

cur.execute("SELECT id, power FROM round_ddos WHERE vicNPC = 0 ORDER BY power DESC")
rank = 0
for ddosID in cur.fetchall():

	rank += 1

	ddosID = ddosID[0]

	cur.execute("UPDATE ranking_ddos SET rank = "+str(rank)+" WHERE ddosID = "+str(ddosID))


db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s\n"