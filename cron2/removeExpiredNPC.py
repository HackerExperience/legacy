import MySQLdb
import time

start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

cur.execute("	SELECT npc_expire.npcID, npc.npcIP \
				FROM npc_expire \
				LEFT JOIN npc ON npc.id = npc_expire.npcID \
				WHERE TIMESTAMPDIFF(SECOND, NOW(), expireDate) < 0 \
			")

for npcID, npcIP in cur.fetchall():

	npcID = str(npcID)
	npcIP = str(npcIP)

	cur.execute("	SELECT id, softType \
					FROM software \
					WHERE userID = "+npcID+" AND isNPC = 1 AND (softType = 30 OR softType = 31)\
				")

	for softID, softType in cur.fetchall():

		if softType == 30: 

			cur.execute("	DELETE \
							FROM software_texts \
							WHERE userID = "+npcID+" AND isNPC = 1 \
						")

		else:  

			cur.execute("	DELETE \
							FROM software_folders \
							WHERE folderID = "+str(softID)+" \
						")

	cur.execute("	DELETE npc, hardware, npc_expire, software, software_running, npc_reset \
					FROM npc_expire \
					LEFT JOIN npc ON npc.id = npc_expire.npcID \
					LEFT JOIN npc_reset ON npc_reset.npcID = npc_expire.npcID \
					LEFT JOIN hardware ON ( \
						hardware.userID = npc.id AND \
						hardware.isNPC = 1 \
					) \
					LEFT JOIN software ON ( \
						software.userID = npc.id AND \
						software.isNPC = 1 \
					)\
					LEFT JOIN software_running ON ( \
						software_running.userID = npc.id AND \
						software_running.isNPC = 1 \
					)\
					WHERE npc_expire.npcID = "+npcID+" \
				")

	#delete from lists

	cur.execute("	SELECT id, userID \
					FROM lists \
					WHERE ip = "+npcIP+"\
				")

	for listID, userID in cur.fetchall():

		listID = str(listID)
		userID = str(userID)

		cur.execute("	INSERT INTO lists_notifications \
							(userID, ip, notificationType) \
						VALUES \
							('"+userID+"', '"+npcIP+"', '1') \
					")

	cur.execute("	DELETE lists, lists_specs \
					FROM lists \
					LEFT JOIN lists_specs \
					ON lists_specs.listID = lists.id\
					WHERE ip = "+npcIP+"\
				")

	cur.execute("	DELETE \
					FROM virus_ddos \
					WHERE ip = "+npcIP+"\
				")

	cur.execute("	DELETE \
					FROM virus \
					WHERE installedIp = "+npcIP+"\
				")

	cur.execute("	DELETE \
					FROM internet_connections \
					WHERE ip = "+npcIP+"\
				")

db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s\n"