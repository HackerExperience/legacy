# -*- encoding: utf-8 -*-

import sys
import MySQLdb
import json
import os
import gettext
import locale

def prepare():

	global userBadge
	global badge_table
	global field
	global db
	global cur
	global badgeInfo
	global issetExtra

	if userBadge == 'user':
		userBadge = True
		badge_table = 'users_badge'
		field = 'userID'
	else:
		userBadge = False
		badge_table = 'clan_badge'
		field = 'clanID'

	db = MySQLdb.connect(host="localhost",user="he",passwd="REDACTED",db="game",charset="utf8",init_command="set names utf8")
	cur = db.cursor()

	json_data = open('/var/www/json/badges.json').read()
	badgeList = json.loads(json_data)
	badgeInfo = {'name':badgeList[str(badgeID)]["name"], 'desc':badgeList[str(badgeID)]["desc"], 'collectible':badgeList[str(badgeID)]["collectible"], 'per_round':badgeList[str(badgeID)]["per_round"]}

	try:
		badgeInfo['extra'] = badgeList[str(badgeID)]["extra"]
		issetExtra = True
	except KeyError:
		issetExtra = False

def badge_isset():

	cur.execute('	SELECT \
						COUNT(*) AS total \
					FROM %s \
					WHERE %s = %s AND badgeID = %s \
					LIMIT 1 \
				' % (badge_table, field, userID, badgeID))

	for totalBadges in cur.fetchall():
		if totalBadges[0] > 0:
			return True
		return False

def badge_haveThisRound(curRound):

	cur.execute('	SELECT COUNT(*) AS total \
					FROM %s \
					WHERE badgeID = %s AND round = %s \
					LIMIT 1 \
				'	% (badge_table, badgeID, curRound))

	for totalBadges in cur.fetchall():
		if totalBadges[0] > 0:
			return True
		return False

def badge_count(search_type):

	if search_type == 1:
		search = badgeID
		where = 'badgeID'
		select = 'COUNT(distinct badgeID)'
	elif search_type == 2:
		search = userID
		where = 'userID'
		select = 'COUNT(distinct badgeID)'
	else:
		search = badgeID
		where = 'badgeID'
		select = 'COUNT(badgeID)'

	cur.execute('	SELECT %s \
					FROM users_badge \
					WHERE %s = %s \
				'	% (select, where, search))

	for totalBadges in cur.fetchall():
		return totalBadges[0]

def badge_validDelay(delay):

	cur.execute('	SELECT COUNT(*) AS total \
					FROM users_badge \
					WHERE \
						TIMESTAMPDIFF(DAY, dateAdd, NOW()) < %s AND \
						badgeID = %s AND \
						userID = %s \
				', (delay, badgeID, userID))
	for totalBadges in cur.fetchall():
		if totalBadges[0] > 0:
			return False
		return True

def cur_round():

	cur.execute("SELECT id FROM round ORDER BY id DESC LIMIT 1")

	for curRound in cur.fetchall():
		return curRound[0]

def user_name():

	cur.execute("SELECT login FROM users WHERE id = "+str(userID))

	for username in cur.fetchall():
		return username[0]

def mail(subject, message):

	cur.execute('	INSERT INTO mails \
						(mails.from, mails.to, mails.type, subject, mails.text, dateSent) \
					VALUES \
						(%s, %s, "", %s, %s, NOW()) \
				', ('-7', str(userID), subject, message))

def get_lang(userID):

	cur.execute("SELECT lang FROM users_language WHERE userID = "+str(userID))

	for lang in cur.fetchall():
		return lang[0]

def install_gettext(lang):

	if lang == 'en':
		lang = 'en_US'
	elif lang == 'br':
		lang = 'pt_BR'

	locale.setlocale(locale.LC_ALL, lang)
	loc = locale.getlocale()

	filename = "/var/www/locale/%s/LC_MESSAGES/messages.mo" % locale.getlocale()[0]
	 
	global trans

	try:
	    trans = gettext.GNUTranslations(open( filename, "rb") )
	except IOError:
	    trans = gettext.NullTranslations()

	trans.install(unicode=True)

def badge_add():

	prepare()
	badgeIsset = badge_isset()

	if not badgeIsset or badgeInfo['collectible']:

		curRound = cur_round()
		valid = True

		if badgeIsset and badgeInfo['per_round']:
			if badge_haveThisRound(curRound):
				valid = False

		if issetExtra:

			try:
				delay = badgeInfo['extra']['delay']
				if not badge_validDelay(delay):
					valid = False
			except KeyError:
				pass

		if not valid:
			return

		cur.execute("	INSERT INTO "+badge_table+" \
							("+field+", badgeID, round, dateAdd) \
						VALUES \
							("+str(userID)+", "+str(badgeID)+", "+str(curRound)+", NOW())\
					")
		if userBadge:

			if not badgeIsset or badgeID != 13:
				
				awardedPlayers = badge_count(1)
				myBadges = badge_count(2)

				install_gettext(get_lang(userID))
				
				subject = _('You earned a new badge!')
				text = _('Hello there, %s. You earned a new badge named <strong>%s</strong>, go check it in <a href="profile">your profile</a>.<br/>') % (user_name(), _(badgeInfo['name']))

				if awardedPlayers <= 0:
					text += _('Feel special: you are the first player to receive this badge! ')
				elif awardedPlayers == 1:
					text += _('Only one other player received this badge. ')
				else:
					text += _('Other %s players received this badge. ') % str(awardedPlayers)

				if myBadges == 1:
					text += _('Enjoy your first badge :)')
				else:
					text += _('You now have a total of <strong>%s</strong> badges.') % str(myBadges)
					if myBadges == 30:
						os.system('python /var/www/python/badge_add.py user '+str(userID)+' 50')

				mail(subject.encode('utf-8').decode('cp1252'), text.encode('utf-8').decode('cp1252'))

			os.system('python /var/www/python/profile_generator.py '+str(userID)+' '+get_lang(userID))

		db.commit()


userBadge = userID = badgeID = None

if __name__ == '__main__':

	userBadge = sys.argv[1]
	userID = int(sys.argv[2])
	badgeID = int(sys.argv[3])

	badge_add()