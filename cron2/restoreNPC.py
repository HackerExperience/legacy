import MySQLdb
import time
start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

#Delete softwares that do not belong to the NPC

cur.execute("	DELETE software, software_running, software_texts, software_folders \
				FROM software \
				LEFT JOIN software_original ON ( \
					software.userID = software_original.npcID AND \
					software.softName = software_original.softName AND \
					software.softVersion = software_original.softVersion AND \
					software.softHidden = 0 \
				) \
				LEFT JOIN software_running ON software_running.softID = software.id \
				LEFT JOIN software_texts ON software_texts.id = software.id \
				LEFT JOIN software_folders ON software_folders.folderID = software.id \
				LEFT JOIN npc_key ON npc_key.npcID = software.userID \
				WHERE software_original.id IS NULL AND software.isNPC = 1 AND software.softType < 32 AND software.softType <> 26 AND npc_key.npcID IS NOT NULL \
			")

#Insert softwares that belogn to the NPC but are not there.

cur.execute("	SELECT software_original.npcID, software_original.softName, software_original.softVersion, software_original.softSize, \
				software_original.softRam, software_original.softType, software_original.running, software_original.licensedTo \
				FROM software \
				RIGHT JOIN software_original ON ( \
					software.userID = software_original.npcID AND \
					software.softName = software_original.softName AND \
					software.softVersion = software_original.softVersion\
				) \
				WHERE software.id IS NULL \
 			")

insert = []

for npcID, name, version, size, ram, softType, running, licensed in cur.fetchall():

	if size <= 0:
		size = 1

	insert.append([npcID, name, version, size, ram, softType, licensed])

cur.executemany("	INSERT INTO software \
						(id, userID, softName, softVersion, softSize, softRam, softType, licensedTo, isNPC) \
					VALUES \
						('', %s, %s, %s, %s, %s, %s, %s, 1) \
				", insert)

#Run original software that should be running but its not.

cur.execute("	SELECT t.id, t.userID, t.softRam \
				FROM (\
					SELECT software.id, software.userID, software.softRam \
					FROM software\
					INNER JOIN software_original ON ( \
						software.userID = software_original.npcID AND \
						software.softName = software_original.softName AND \
						software.softVersion = software_original.softVersion \
					)\
					WHERE software_original.running = 1 AND software.isNPC = 1 \
				) t \
				LEFT JOIN software_running ON t.id = software_running.softID \
				WHERE software_running.softID IS NULL \
			")

insert = []

for softID, npcID, ram in cur.fetchall():

	insert.append([softID, npcID, ram])

cur.executemany("	INSERT INTO software_running \
						(id, softID, userID, ramUsage, isNPC) \
					VALUES \
						('', %s, %s, %s, 1) \
				", insert)

db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s\n"