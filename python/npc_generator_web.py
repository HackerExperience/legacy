import MySQLdb
import json
import string

db = MySQLdb.connect(host="localhost",user="he",passwd="REDADCTED",db="game")
cur = db.cursor()

json_data = open('/var/www/json/npc.json').read()
npcList = json.loads(json_data)

def match_slash(txt):

	subArray = None
	subgroup = txt.split('/')
	for id, key in enumerate(subgroup):

		if key == '<':
			#HTML bug fix (</span>)
			break

		if id == 0:
			subArray = None
			baseArray = npcList
		else:
			baseArray = subArray

		try:
			subArray = baseArray[key]
		except KeyError:
			pass

	return subArray

def getIP(key):

	cur.execute("""	SELECT INET_NTOA(npc.npcIP)
					FROM npc_key 
					INNER JOIN npc
					ON npc.id = npc_key.npcID
					WHERE npc_key.key = %s
					LIMIT 1
				""", key)
	for ip in cur.fetchall():
		return ip[0]

	return 'Unknown IP'

def choose_language(info, language):

	#info['en']
	if language == 'en':
		return info['en']
	else:
		try:
			info[language]
			return info[language]
		except KeyError:
			return info['en']


def getInfo(npcInfo, match, language = 'en'):

	if not npcInfo:
		return

	try:
		return choose_language(npcInfo, language)
	except KeyError:
		pass

	if match[-2::] == 'ip':
		return getIP(match[:-3:])

def web_format(txt, language):

	parted = txt.split('::')
	if len(parted) == 1:
		return txt;

	for match in parted:

		try:
			value = getInfo(npcList[match], match, language)
			continue
		except KeyError:
			value = getInfo(match_slash(match), match, language)

		print value

		if value:
			txt = txt.replace('::'+match+'::', value)

	return txt

def add(npcType, npcInfo, key):
	
	#ACID Transaction
	try:

		cur.execute(""" SELECT npcID
						FROM npc_key
						WHERE npc_key.key = %s
						LIMIT 1
					""", key)

		for npcID in cur.fetchall():
			npcID = str(npcID[0])

		#add to npc_info_lang

		for language in npcInfo['name']:

			npcName = npcInfo['name'][language]
			npcWeb = web_format(npcInfo['web'][language], language)
			table = 'npc_info_'+language

			cur.execute(""" UPDATE """+table+"""
							SET
								web = %s,
								name = %s
							WHERE npcID = %s
						""", (npcWeb.encode('utf-8').decode('cp1252'), npcName.encode('utf-8').decode('cp1252'), npcID))

		db.commit()

	except:
		print 'Rolling back ' + key
		db.rollback()

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

	#WHOIS_MEMBER, HIRER
	for level in npcList[npcType]:
		numType = npcList[npcType][level]['type']

		if numType != 61:
			for key in npcList[npcType][level]:
				if key != 'type':
					add(numType, npcList[npcType][level][key], npcType+'/'+level+'/'+key)

		continue

db.commit()