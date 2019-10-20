import MySQLdb
import os

db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game")
cur = db.cursor()

cur.execute("SELECT id FROM round WHERE status = 0 AND TIMESTAMPDIFF(SECOND, startDate, NOW()) > 0 LIMIT 1")

for roundID in cur.fetchall():

	# 2019: NPCs need to be generated BEFORE newroundupdater
	#NPCs precisam ser gerados ANTES do newroundupdater
	os.system('python /var/www/python/npc_generator.py')

	import random
	import string

	def news(title, content):
		cur.execute('	INSERT INTO news \
							(author, title, content, news.date, news.type) \
						VALUES \
							(%s, %s, %s, NOW(), %s) \
					', ('-8', title, content, ''))
	def ip_generator():
	    return ".".join([str(random.randrange(1,255)),str(random.randrange(0,255)),str(random.randrange(0,255)),str(random.randrange(0,255))])

	def pwd_generator(size=8, chars=string.ascii_uppercase + string.digits + string.ascii_lowercase):
		return ''.join(random.choice(chars) for x in range(size))

	def acc_generator(size=6, chars=string.digits):
		first = random.choice(chars)
		while first == '0':
			first = random.choice(chars)
		return first + ''.join(random.choice(chars) for x in range(size - 1))

	def firstBankID():
		cur.execute("SELECT id FROM npc WHERE npcType = 1 LIMIT 1")
		for bankID in cur.fetchall():
			return bankID[0]


	cur.execute("UPDATE users_stats SET lastIpReset = NOW(), lastPwdReset = NOW()")

	cur.execute("SELECT id FROM users")
	for userID in cur.fetchall():

		cur.execute("""	INSERT INTO hardware
							(userID, name)
						VALUES
							(%s, 'Server #1')
					""", str(userID[0]))

		cur.execute("""	UPDATE users
						SET 
							gameIP = INET_ATON(%s),
							gamePass = %s
						WHERE id = %s
						LIMIT 1
					""", (ip_generator(), pwd_generator(), str(userID[0])))

		bankID = firstBankID()

		cur.execute("""	INSERT INTO bankAccounts
							(bankAcc, bankPass, bankID, bankUser, cash, dateCreated)
						VALUES
							(%s, %s, %s, %s, '0', NOW())
					""", (acc_generator(), pwd_generator(6), bankID, str(userID[0])))

	cur.execute("SELECT clanID, clanIP FROM clan")
	for clanID, oldClanIP in cur.fetchall():

		clanIP = ip_generator()

		cur.execute("""	INSERT INTO npc
							(npcType, npcIP, npcPass)
						VALUES
							(10, INET_ATON(%s), %s)
					""", (clanIP, pwd_generator()))

		npcID = str(db.insert_id())

		cur.execute("""	UPDATE clan
						SET 
							clanIP = INET_ATON(%s)
						WHERE clan.clanID = %s
					""", (clanIP, str(clanID)))

		cur.execute("""	INSERT INTO hardware
							(userID, name, isNPC)
						VALUES
							(%s, 'Server #1', 1)
					""", str(npcID))

	cur.execute("DELETE FROM users_online")
	cur.execute("UPDATE round SET status = 1 WHERE id = %s", str(roundID[0]))
	cur.execute("INSERT INTO round_stats (id) VALUES (%s)", str(roundID[0]))


	os.system('python /var/www/cron2/updateRanking.py')
	os.system('python /var/www/python/rank_generator.py')

	title = 'Round #'+str(roundID[0])+' started'
	content = 'Ye\'all, get ready to hack! Round '+str(roundID[0])+' just started.'

	news(title, content)

	db.commit()

#TODO: funcao para verificar se existe(m) ips repetidos
