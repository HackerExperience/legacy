import MySQLdb
import time
start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

cur.execute("	SELECT npc_reset.npcID, npc.npcIP\
				FROM npc_reset \
				INNER JOIN npc \
				ON npc.id = npc_reset.npcID \
				WHERE TIMESTAMPDIFF(SECOND, NOW(), npc_reset.nextScan) < 0 \
			")

for npcID, npcIP in cur.fetchall():

	npcID = str(npcID)
	npcIP = str(npcIP)

	cur.execute("	SELECT software.id, software.softName, software.softVersion, software.softType \
					FROM software\
					INNER JOIN (\
						SELECT virus.virusID \
						FROM virus \
						WHERE virus.installedip = '"+npcIP+"' \
					) v ON v.virusID = software.id \
				")

	for virusID, softName, softVersion, softType in cur.fetchall():

		virusID = str(virusID)

		#Get userID who will be affected by the deletion

		cur.execute("	SELECT userID \
						FROM lists \
						WHERE virusID = "+virusID+"\
					")

		for userID in cur.fetchall():

			userID = str(userID[0])
			
			virusName = softName

			if softType == 97:
				virusName += '.vddos '
			elif softType == 98:
				virusName += '.vwarez '
			elif softType == 99:
				virusName += '.vpsam '	

			virusName += str(softVersion)
			
			#Add notifications to those users who will have the virus deleted.

			cur.execute("	INSERT INTO lists_notifications \
								(userID, ip, notificationType, virusName) \
							VALUES \
								('"+userID+"', '"+npcIP+"', '3', '"+virusName+"') \
						")

	#Remove from the list

	cur.execute("	UPDATE lists \
					SET \
						virusID = 0 \
					WHERE ip = "+npcIP+"\
				")

	#Delete the virus

	cur.execute("	DELETE \
					FROM virus_ddos \
					WHERE ip = "+npcIP+"\
				")

	cur.execute("	DELETE \
					FROM virus \
					WHERE installedIp = "+npcIP+"\
				")

	cur.execute("	DELETE \
					FROM software_running \
					WHERE userID = '"+npcID+"' AND isNPC = '1'\
				")	

	cur.execute("	DELETE \
					FROM software \
					WHERE userID = '"+npcID+"' AND isNPC = '1'\
				")

scanInterval = '1' #av scan every 7 days.

cur.execute("	UPDATE npc_reset\
				SET \
					nextScan = DATE_ADD(NOW(), INTERVAL '"+scanInterval+"' DAY) \
				WHERE TIMESTAMPDIFF(SECOND, NOW(), npc_reset.nextScan) < 0 \
			")

db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s\n"