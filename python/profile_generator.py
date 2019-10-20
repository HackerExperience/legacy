#!/usr/bin/python

#coding: utf-8

import sys
reload(sys)
sys.setdefaultencoding("utf-8")

import MySQLdb
import hashlib
import json
import os
import locale
import gettext

def __(string):
	return _(string).encode("UTF-8").decode("UTF-8")

def install_gettext(lang):

	if lang == 'en':
		lang = 'en_US'
	elif lang == 'br' or lang == 'pt':
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

json_data = open('/var/www/json/badges.json').read()
badgeList = json.loads(json_data)

def getBadgeInfo(badgeID):

	badgeID = str(badgeID)

	return [badgeList[badgeID]["name"], badgeList[badgeID]["desc"], badgeList[badgeID]["collectible"]]

def save(html, userID, lang):

	f = open('/var/www/html/profile/'+userID+'_'+lang+'.html', 'w')
	f.write(html.encode("UTF-8"))
	f.close()



def format(number):

	if number > 0 and number < 1:
		return str(number)

	s = '%d' % number
	groups = []
	while s and s[-1].isdigit():
		groups.append(s[-3:])
		s = s[:-3]
	return s + ','.join(reversed(groups))

def PlayTime(seconds):

	days = seconds / 86400
	seconds -= 86400*days
	hrs = seconds / 3600
	seconds -= 3600 * hrs

	if days == 0:
		mins = seconds / 60

		ret = trans.ngettext('%d hour', '%d hours', hrs) % hrs
		if hrs == 0:
			ret = ''
		elif mins != 0:
			ret += " "+__("and")+" "

		if mins != 0:
			ret += trans.ngettext('%d minute', '%d minutes', mins) % mins

	else:

		ret = trans.ngettext('%d day', '%d days', days) % days
		if hrs != 0:
			ret += " "+__("and")+" "
			ret += trans.ngettext('%d hour', '%d hours', hrs) % hrs

	return str(ret)

db = MySQLdb.connect(host="localhost",user="he",passwd="REDADCTED",db="game")
cur = db.cursor()

userID = str(sys.argv[1])

try:
	lang = str(sys.argv[2])
except:
	lang = 'en'

install_gettext(lang)

cur.execute("	SELECT \
					users.login, users.premium, clan.clanID, clan.name, clan.nick, clan.createdBy, ranking_user.rank, DATEDIFF(CURDATE(), users_stats.dateJoined) AS gameAge,\
					users_stats.exp, users_stats.timeplaying, users_stats.hackCount, users_stats.ddosCount, users_stats.warezSent, users_stats.spamSent,\
					users_stats.ipResets, users_stats.moneyEarned, users_stats.moneyTransfered, users_stats.moneyHardware, users_stats.moneyResearch, users_stats.profileViews,\
					(SELECT COUNT(*) FROM missions_history WHERE missions_history.userID = users.id AND completed = 1), users_admin.userID \
				FROM users\
				LEFT JOIN clan_users\
				ON clan_users.userID = users.id\
				LEFT JOIN clan \
				ON clan.clanID = clan_users.clanID\
				INNER JOIN users_stats\
				ON users_stats.uid = users.id\
				LEFT JOIN ranking_user\
				ON ranking_user.userID = users.id\
				LEFT JOIN users_admin\
				ON users_admin.userID = users.id\
				WHERE users.id = '"+ userID +"'\
				LIMIT 1 \
			")

for login, premium, clanID, clanName, clanTag, clanOwner, ranking, gameAge, reputation, timePlaying, hackCount, ddosCount, warezSent, spamSent, ipResets, moneyEarned, moneyTransfered, moneyHardware, moneyResearch, profileViews, missionCount, admin in cur.fetchall():	

	if not gameAge:
		gameAge = 0

	if ranking == -1:

		cur.execute("	SELECT COUNT(*) AS total \
						FROM ranking_user \
					")

		for rank in cur.fetchall():

			ranking = rank

	if clanName:

		masterBadge = ''
		if str(clanOwner) == userID:
			masterBadge = '<span class="label label-info right">'+__('Master')+'</span>'

		nick = '['+clanTag+'] '+login
		clan = '<tr>\n\
						<td><span class="item">'+__('Clan')+'</span></td>\n\
						<td><a href="clan?id='+str(clanID)+'" class="black">['+str(clanTag)+'] '+str(clanName)+'</a>'+masterBadge+'</td>\n\
					</tr>\n'
	else:
		nick = login
		clan = '\n'

	cur.execute("	SELECT \
						COUNT(*) AS total \
					FROM users_friends \
					WHERE userID = '"+userID+"' OR friendID = '"+userID+"'\
				")

	for totalFriends in cur.fetchall():
		totalFriends = totalFriends[0]

	cur.execute("	SELECT \
						userID, friendID \
					FROM users_friends \
					WHERE userID = '"+userID+"' OR friendID = '"+userID+"'\
					ORDER BY dateAdd ASC \
					LIMIT 5 \
				")

	friendsHTML = ''
	for friendID1, friendID2 in cur.fetchall():

		if str(friendID1) == userID:
			friendID = str(friendID2)
		else:
			friendID = str(friendID1)

		cur.execute("	SELECT \
							login, \
							cache.reputation, \
							ranking_user.rank, \
							clan.name, clan.clanID \
						FROM users \
						LEFT JOIN cache \
						ON cache.userID = users.id \
						LEFT JOIN ranking_user \
						ON ranking_user.userID = users.id \
						LEFT JOIN clan_users\
						ON clan_users.userID = users.id \
						LEFT JOIN clan \
						ON clan.clanID = clan_users.clanID \
						WHERE users.id = '"+friendID+"' \
						LIMIT 1 \
					")

		for friendName, friendReputation, friendRank, friendClanName, friendClanID in cur.fetchall():

			friendClanHTML = '\n'
			if friendClanName:
				friendClanHTML = '\n\
											<span class="he16-clan heicon"></span>\n\
											<small><a href="clan?id='+str(friendClanID)+'">'+str(friendClanName)+'</a></small>\n'

			if not friendReputation:
				friendReputation = 0

			friendPic = 'images/profile/thumbnail/'+str(hashlib.md5(str(friendName+friendID)).hexdigest())+'.jpg'
			if not os.path.isfile('/var/www/'+friendPic):
				friendPic = 'images/profile/thumbnail/unsub.jpg'

			friendsHTML += '\n\
                        <ul class="list">\n\
                            <a href="profile?id='+friendID+'">\n\
                                <li  class="li-click">\n\
                                    <div class="span2 hard-ico">\n\
                                        <img src="'+friendPic+'">\n\
                                    </div>\n\
                                    <div class="span10">\n\
                                        <div class="list-ip">\n\
                                            '+str(friendName)+'\n\
                                        </div>\n\
                                        <div class="list-user">\n\
                                            <span class="he16-reputation heicon"></span>\n\
                                            <small>'+format(friendReputation)+'</small>\n\
                                            <span class="he16-ranking heicon"></span>\n\
                                            <small>#'+format(friendRank)+'</small>'+friendClanHTML+'\
                                        </div>\n\
                                    </div>\n\
                                    <div style="clear: both;"></div>\n\
                                </li>\n\
                            </a>\n\
                        </ul>\n\
                        '

    	friendsHTML += '<div class="center">'

	if totalFriends > 5:
		friendsHTML += '<a href="profile?id='+userID+'&view=friends" class="btn btn-inverse">View all</a>&nbsp;&nbsp;'
	
	elif totalFriends == 0:
		friendsHTML += __('Oh no! This user has no friends :(')+'<br/><br/>'

	friendsHTML += '<a href="profile?view=friends&add='+userID+'" class="btn btn-success add-friend" value="'+userID+'">'+__('Add friend')+'</a></div>'

	totalBadges = 0
	htmlBadges = ''

	cur.execute("	SELECT \
						users_badge.badgeID, \
						COUNT(users_badge.badgeID) \
					FROM users_badge \
					JOIN badges_users \
					ON badges_users.badgeID = users_badge.badgeID\
					WHERE users_badge.userID = '"+userID+"' \
					GROUP BY users_badge.badgeID \
					ORDER BY badges_users.priority, badges_users.badgeID \
				")

	for badgeID, badgeTotal in cur.fetchall():

		totalBadges += 1
		badgeInfo = getBadgeInfo(badgeID)

		badgeStr = '<strong>'+__(badgeInfo[0])+'</strong>'
		if badgeInfo[1]:
			badgeStr += ' - '+__(badgeInfo[1])

		if badgeInfo[2]:
			badgeStr += '<br/><br/>'+trans.ngettext('Awarded %d time', 'Awarded %d times', badgeTotal) % badgeTotal

		htmlBadges += '<img src="images/badges/'+str(badgeID)+'.png" class="profile-tip" title="'+badgeStr+'" value="'+str(badgeID)+'"/>'


	if not totalBadges:
		htmlBadges = __('This player have no badges.')

	staffBadge = ''
	if admin:
		staffBadge = '<span class="label label-important">'+__('Staff')+'</span>'

	pic = 'images/profile/'+str(hashlib.md5(login+userID).hexdigest())+'.jpg'
	if not os.path.isfile('/var/www/'+pic):
		pic = 'images/profile/unsub.jpg'

	html = '\n\
	<span id="modal"></span>\n\
	<div class="widget-box">\n\
		<div class="widget-title">\n\
			<span class="icon"><i class="he16-pda"></i></span>\n\
			<h5>'+nick+'</h5>\n\
			'+staffBadge+'\n\
		</div>\n\
		<div class="widget-content nopadding">\n\
			<table class="table table-cozy table-bordered table-striped table-fixed">\n\
				<tbody>\n\
					<tr>\n\
						<td><span class="item">'+__('Reputation')+'</span></td>\n\
						<td>'+format(reputation)+' <span class="small">('+__('Ranked')+' #'+format(ranking)+')</span></td>\n\
					</tr>\n\
					<tr>\n\
						<td><span class="item">'+__('Age')+'</span></td>\n\
						<td>'+PlayTime(gameAge * 86400)+'</td>\n\
					</tr>\n\
					<tr>\n\
						<td><span class="item">'+__('Time playing')+'</span></td>\n\
						<td>'+PlayTime(int(timePlaying) * 60)+'</td>\n\
					</tr>\n\
					'+clan+'\n\
				</tbody>\n\
			</table>\n\
		</div>\n\
	</div>\n\
	<div class="widget-box">\n\
		<div class="widget-title">\n\
			<span class="icon"><i class="he16-stats"></i></span>\n\
			<h5>'+__('Stats')+'</h5>\n\
		</div>\n\
		<table class="table table-cozy table-bordered table-striped table-fixed">\n\
			<tbody>\n\
				<tr>\n\
					<td><span class="item">'+__('Hack count')+'</span></td>\n\
					<td>'+format(hackCount)+'</td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('IP Resets')+'</span></td>\n\
					<td>'+format(ipResets)+'</td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('Servers used to DDoS')+'</span></td>\n\
					<td>'+format(ddosCount)+'</td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('Spam sent')+'</span></td>\n\
					<td>'+format(spamSent)+' '+__('mails')+'</td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('Warez uploaded')+'</span></td>\n\
					<td>'+format(warezSent)+' GB</td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('Missions completed')+'</span></td>\n\
					<td>'+format(missionCount)+'</td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('Profile clicks')+'</span></td>\n\
					<td>'+format(profileViews)+'</td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('Money earned')+'</span></td>\n\
					<td><font color="green">$'+format(moneyEarned)+'</font></td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('Money transfered')+'</span></td>\n\
					<td><font color="green">$'+format(moneyTransfered)+'</font></td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('Money spent on hardware')+'</span></td>\n\
					<td><font color="green">$'+format(moneyHardware)+'</font></td>\n\
				</tr>\n\
				<tr>\n\
					<td><span class="item">'+__('Money spent on research')+'</span></td>\n\
					<td><font color="green">$'+format(moneyResearch)+'</font></td>\n\
				</tr>\n\
			</tbody>\n\
		</table>\n\
	</div>\n\
	<div class="center"><a class="btn btn-inverse center" type="submit">'+__('Switch to All-Time stats')+'</a></div>\n\
</div>\n\
<div class="span4">\n\
	<div class="widget-box">\n\
		<div class="widget-title">\n\
			<span class="icon"><span class="he16-profile"></span></span>\n\
			<h5>'+__('Photo & Badges')+'</h5>\n\
			<span class="label label-info">'+str(totalBadges)+'</span>\n\
		</div>\n\
		<div class="widget-content padding noborder">\n\
	        <div class="span12">\n\
				<div class="span12" style="text-align: center; margin-right: 15px; margin-bottom: 5px;">\n\
					<img src="'+pic+'">\n\
				</div>\n\
                <div class="row-fluid">\n\
                    <div class="span12 badge-div">\n\
                        '+htmlBadges+'\
                	</div>\n\
            	</div>\n\
            </div>\n\
		</div>\n\
		<div style="clear: both;" class="nav nav-tabs">&nbsp;</div>\n\
	</div>\n\
<div class="widget-box">\n\
	<div class="widget-title">\n\
		<span class="icon"><i class="he16-clan"></i></span>\n\
		<h5>'+__('Friends')+'</h5>\n\
		<a href="profile?id='+userID+'&view=friends"><span class="label label-info">'+str(totalFriends)+'</span></a>\n\
	</div>\n\
	<div class="widget-content padding">\n\
	'+friendsHTML+'\
	</div>\n\
	'

save(html, userID, lang)

cur.execute('INSERT INTO cache_profile \
				(userID, expireDate) \
			VALUES \
				('+str(userID)+', NOW())\
 				ON DUPLICATE KEY UPDATE expireDate = NOW()')
cur.execute('UPDATE cache SET reputation = %s WHERE userID = %s' % (reputation, userID))

db.commit()