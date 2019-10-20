import MySQLdb
import time
start_time = time.time()

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

cur.execute("""	SELECT 
					id
				FROM npc
				INNER JOIN npc_key ON npc_key.npcID = npc.id
			""")
rank = 0
for npcID in cur.fetchall():

	cur.execute("""	UPDATE hardware
					SET 
						hdd = 10000,
						cpu = 8000,
						net = 50,
						ram = 1024
					WHERE 
						isNPC = 1 AND
						userID = """+str(npcID[0])+"""
				""")

db.commit()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s\n"