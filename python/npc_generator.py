import MySQLdb
import json
import random
import string

db = MySQLdb.connect(host="localhost",user="he",passwd="REDADCTED",db="game")
cur = db.cursor()

json_data = open('/var/www/json/npc.json').read()
npcList = json.loads(json_data)

def ip_generator():
    return ".".join([str(random.randrange(0,255)),str(random.randrange(0,255)),str(random.randrange(0,255)),str(random.randrange(1,255))])

def pwd_generator(size=8, chars=string.ascii_uppercase + string.digits + string.ascii_lowercase):
	return ''.join(random.choice(chars) for x in range(size))

def emptyDB():

	cur.execute("""	DELETE npc, hardware, software, log
					FROM npc 
					LEFT JOIN hardware
					ON 
						hardware.userID = npc.id AND
						hardware.isNPC = 1
					LEFT JOIN software
					ON
						software.userID = npc.id AND
						software.isNPC = 1
					LEFT JOIN log
					ON 
						log.userID = npc.id AND
						log.isNPC = 1
					WHERE 
						npc.npcType != 80
					""")

	cur.execute("DELETE FROM software_original")
	cur.execute("DELETE FROM software_running")
	cur.execute("DELETE FROM npc_key")
	cur.execute("DELETE FROM npc_info_en")
	cur.execute("DELETE FROM npc_info_pt")
	cur.execute("DELETE FROM npc_reset")

	db.commit()

def add(npcType, npcInfo, key):
	
	#ACID Transaction
	try:

		#add to npc

		try:
			npcIP = npcInfo['ip']
		except KeyError:
			npcIP = ip_generator()

		cur.execute(""" INSERT INTO npc
							(npcType, npcIP, npcPass)
						VALUES
							(%s, INET_ATON(%s), %s)
					""", (npcType, npcIP, pwd_generator()))

		npcID = str(db.insert_id())

		#add to npc_info_lang

		for language in npcInfo['name']:

			npcName = npcInfo['name'][language]
			npcWeb = npcInfo['web'][language]
			table = 'npc_info_'+language

			cur.execute(""" INSERT INTO """+table+"""
								(npcID, name, web)
							VALUES
								(%s, %s, %s)
						""", (npcID, npcName, npcWeb))

		#add to npc_key

		cur.execute(""" INSERT INTO npc_key
							(npcID, npc_key.key)
						VALUES
							(%s, %s)
					""", (npcID, key))

		#add to hardware

		cpu = npcInfo['hardware']['cpu']
		hdd = npcInfo['hardware']['hdd']
		ram = npcInfo['hardware']['ram']
		net = npcInfo['hardware']['net']
		
		cur.execute(""" INSERT INTO hardware
							(userID, name, cpu, hdd, ram, net, isNPC)
						VALUES
							(%s, '', %s, %s, %s, %s, '1')
					""", (npcID, cpu, hdd, ram, net))

		#add to software_original

		#no final do arquivo, que chama o python `software_generator.py`

		#add log

		cur.execute("""	INSERT INTO log
							(userID, isNPC)
						VALUES
							(%s, 1)
					""", npcID)

		nextScan = random.randint(1,50)

		cur.execute("""	INSERT INTO npc_reset
							(npcID, nextScan)
						VALUES
							(%s, DATE_ADD(NOW(), INTERVAL %s HOUR))
					""", (npcID, nextScan))

	except:
		print 'ROOOLING BACK'
		db.rollback()

emptyDB()

for npcType in npcList:
	
	try:
		npcList[npcType]['hardware']
		#MD, FBI, NSA, ISP, EVILCORP, SAFENET

		add(npcList[npcType]['type'], npcList[npcType], npcType)

		continue
	except KeyError:
		pass

	try:
		numType = npcList[npcType]['type']
		#WHOIS, BANK, NPC, PUZZLE

		for key in npcList[npcType]:
			if key != 'type':
				add(numType, npcList[npcType][key], npcType+'/'+key)

		continue
	except KeyError:
		pass

	#HIRER
	for level in npcList[npcType]:
		numType = npcList[npcType][level]['type']

		if numType != 61:
			for key in npcList[npcType][level]:
				if key != 'type':
					add(numType, npcList[npcType][level][key], npcType+'/'+level+'/'+key)

		continue

db.commit()


# from subprocess import call
# call(["python","software_generator.py"])
# call(["python","software_generator_riddle.py"])
# call(["python","npc_generator_web.py"])
#precisa ser por os (e nao subprocess.call) pq npc_generator.py eh chamado de newroundupdater por os

import os
os.system('python /var/www/python/software_generator.py')
os.system('python /var/www/python/software_generator_riddle.py')
os.system('python /var/www/python/npc_generator_web.py')