import time
start_time = time.time()

extensionDict = {
    '1' : '.crc',
    '2' : '.hash',
    '3' : '.scan',
    '4' : '.fwl',
    '5' : '.hdr',
    '6' : '.skr',
    '7' : '.av',
    '8' : '.vspam',
    '9' : '.vwarez',
    '10' : '.vddos',
    '11' : '.vcol',
    '12' : '.vbrk',
    '13' : '.exp',
    '14' : '.exp',
    '20' : '.vminer',
}

typeDict = {
	    '1' : 'Cracker',
	    '2' : 'Hasher',
	    '3' : 'Port Scan',
	    '4' : 'Firewall',
	    '5' : 'Hidder',
	    '6' : 'Seeker',
	    '7' : 'Anti-Virus',
	    '8' : 'Spam Virus',
	    '9' : 'Warez Virus',
	    '10' : 'DDoS Virus',
        '11' : 'Virus Collector',
        '12' : 'DDoS Breaker',
        '13' : 'FTP Exploit',
        '14' : 'SSH Exploit',
        '20' : 'BTC Miner',
}

def getExtension(softType):
    return extensionDict.get(str(softType), '.todo')

def getType(softType):
    return typeDict.get(str(softType), 'Unknown')

def dotVersion(version):
	return str(version / 10)+'.'+ str(version % 10)

def save(html, rank, page, preview = False):

	if preview:
		path = '/var/www/html/fame/rank_'+rank+'_preview.html'
	else:
		path = '/var/www/html/ranking/'+rank+'_'+str(page)+'.html'

	f = open(path, 'w')
	f.write(html)
	f.close()

def createRankUsers():

	cur.execute("	SELECT ranking_user.userID, clan_users.clanID, users.login, users_premium.id, users_online.id,  users_stats.exp, clan.nick, clan.name,\
						(	SELECT COUNT(*)\
							FROM lists\
							WHERE lists.userID = ranking_user.userID\
						) AS hackedDB\
		            FROM ranking_user \
					INNER JOIN users\
					 ON ranking_user.userID = users.id\
					INNER JOIN users_stats\
					ON ranking_user.userID = users_stats.uid\
					LEFT JOIN users_online\
					ON ranking_user.userID = users_online.id\
					LEFT JOIN users_premium\
					ON ranking_user.userID = users_premium.id\
					LEFT JOIN clan_users\
					ON ranking_user.userID = clan_users.userID\
					LEFT JOIN clan\
					ON clan.clanID = clan_users.clanID\
					WHERE ranking_user.rank >= 0 \
					ORDER BY ranking_user.rank ASC \
					LIMIT 5000 \
				")

	html = ""
	i = 0
	page = 0
	preview = 0
	previewLimit = 10
	limite = 100

	for userID, clanID, username, premium, online, exp, clanNick, clanName, hackCount in cur.fetchall():

		i += 1

		pos = '<center>'+str(i)+'</center>'

		clan = premiumImg = onlineImg = ''

		if clanID:
			clan = '<a href="clan?id='+str(clanID)+'">['+str(clanNick)+'] '+str(clanName)+'</a>'

		if premium:
			premiumImg = '<span class="r-premium"></span>'

		if online:
			onlineImg = '<span class="r-online"></span>'

		user = '<a href="profile?id='+str(userID)+'">'+str(username)+'</a>'+onlineImg+premiumImg	

		power = '<center>'+str(exp)+'</center>'

		count = '<center>'+str(hackCount)+'</center>'

		html += '\
                                        <tr>\n\
                                            <td>'+pos+'</td>\n\
                                            <td>'+user+'</td>\n\
                                            <td>'+power+'</td>\n\
                                            <td>'+count+'</td>\n\
                                            <td>'+clan+'</td>\n\
                                        </tr>\n\
		\n'

		if page == 0 and (i % previewLimit) == 0 and preview == 0:
			save(html, 'user', '', True)
			preview = 1

		if(i % limite == 0):
			save(html, 'user', page)
			page += 1
			html = ''

	if html != '':
		save(html, 'user', page)

	if preview == 0:
		save(html, 'user', '', True)

def createRankClans():

	cur.execute("	SELECT ranking_clan.clanID, clan.name, clan.nick, clan.slotsUsed, clan.power, clan_war.clanID1, clan_stats.won, clan_stats.lost\
					FROM ranking_clan\
					INNER JOIN clan\
					ON ranking_clan.clanID = clan.clanID \
					INNER JOIN clan_stats\
					ON clan_stats.cid = clan.clanID\
					LEFT JOIN clan_war\
					ON (clan_war.clanID1 = clan.clanID OR clan_war.clanID2 = clan.clanID)\
					WHERE ranking_clan.rank > 0 \
					ORDER BY ranking_clan.rank ASC \
				")

	html = ""
	i = 0
	page = 0
	preview = 0
	previewLimit = 10
	limite = 100

	for clanID, name, nick, members, power, war, won, lost in cur.fetchall():

		i += 1

		pos = '<center>'+str(i)+'</center>'

		label = ''

		if war:
			label = '<span class="r-war"></span>'

		if won == 0 and lost == 0:
			rate = ''
		else:
			rate = ' <span class="small">'+str(int(round(won / float(lost + won), 2) * 100))+' %</span>'

		clanName = '<a href="clan?id='+str(clanID)+'">['+str(nick)+'] '+str(name)+label+'</a>'
		clanPower = '<center>'+str(power)+'</center>'
		clanWL = '<center><font color="green">'+str(won)+'</font> / <font color="red">'+str(lost)+'</font>'+rate+'</center>'
		clanMembers = '<center>'+str(members)+'</center>'

		html += '\
                                        <tr>\n\
                                            <td>'+pos+'</td>\n\
                                            <td>'+clanName+'</td>\n\
                                            <td>'+clanPower+'</td>\n\
                                            <td>'+clanWL+'</td>\n\
                                            <td>'+clanMembers+'</td>\n\
                                        </tr>\n\
		\n'

		if page == 0 and (i % previewLimit) == 0 and preview == 0:
			save(html, 'clan', '', True)
			preview = 1

		if(i % limite == 0):
			save(html, 'clan', page)
			page += 1
			html = ''

	if html != '':
		save(html, 'clan', page)
		
	if preview == 0:
		save(html, 'clan', '', True)

def createRankSoft():

	cur.execute("	SELECT ranking_software.softID, r.softwarename, r.userID, r.softwareType, r.newVersion \
                    FROM ranking_software \
                    INNER JOIN software_research r \
                    ON ranking_software.softID = r.softID \
                    ORDER BY ranking_software.rank ASC \
                ")

	html = htmlPreview =""
	i = 0
	page = 0
	preview = 0
	previewLimit = 10
	limite = 100

	for softID, name, userID, softType, version in cur.fetchall():

		i += 1

		pos = '<center>'+str(i)+'</center>'

		extension = getExtension(softType)

		softName = str(name)+extension
		softVersion = '<center>'+dotVersion(version)+'</center>'
		softType = '<a href="?show=software&orderby='+extension+'">'+getType(softType)+'</a>'

		html += '\
                                        <tr>\n\
                                            <td>'+pos+'</td>\n\
                                            <td>'+softName+'</td>\n\
                                            <td>'+softVersion+'</td>\n\
                                            <td>'+softType+'</td>\n\
                                        </tr>\n\
		\n'

		htmlPreview += '\
                                        <tr>\n\
                                            <td>'+pos+'</td>\n\
                                            <td>'+softName+'</td>\n\
                                            <td>'+softVersion+'</td>\n\
                                            <td>Unknown</td>\n\
                                            <td>'+softType+'</td>\n\
                                        </tr>\n\
		\n'

		if page == 0 and (i % previewLimit) == 0 and preview == 0:
			save(htmlPreview, 'soft', '', True)
			preview = 1

		if(i % limite == 0):
			save(html, 'soft', page)
			page += 1
			html = ''

	if html != '':
		save(html, 'soft', page)
		
	if preview == 0:
		save(htmlPreview, 'soft', '', True)

def createRankDDoS():

	cur.execute("	SELECT round_ddos.attID, users.login AS attUser, round_ddos.vicID, round_ddos.power, round_ddos.servers\
                    FROM ranking_ddos\
                    INNER JOIN round_ddos\
                    ON ranking_ddos.ddosID = round_ddos.id\
                    INNER JOIN users\
                    ON users.id = round_ddos.attID \
                    ORDER BY ranking_ddos.rank ASC \
                ")

	html = ""
	i = 0
	page = 0
	preview = 0
	previewLimit = 10
	limite = 10

	for attID, attUser, vicID, power, servers in cur.fetchall():

		i += 1

		pos = '<center>'+str(i)+'</center>'
		attName = '<a href="profile?id='+str(attID)+'">'+str(attUser)+'</a>'
		vicName = 'Unknown'
		power = '<center>'+str(power)+'</center>'
		servers = '<center>'+str(servers)+'</center>'

		html += '\
                                        <tr>\n\
                                            <td>'+pos+'</td>\n\
                                            <td>'+attName+'</td>\n\
                                            <td>'+vicName+'</td>\n\
                                            <td>'+power+'</td>\n\
                                            <td>'+servers+'</td>\n\
                                        </tr>\n\
		\n'

		if page == 0 and (i % previewLimit) == 0 and preview == 0:
			save(html, 'ddos', '', True)
			preview = 1

		if(i % limite == 0):
			save(html, 'ddos', page)
			page += 1
			html = ''

	if html != '':
		save(html, 'ddos', page)
		
	if preview == 0:
		save(html, 'ddos', '', True)

import MySQLdb

db = MySQLdb.connect(host="localhost",user="he",passwd="REDADCTED",db="game")
cur = db.cursor()

createRankUsers()

createRankClans()

createRankSoft()

createRankDDoS()

print time.strftime("%d/%m/%y %H:%M:%S"),' - ',__file__,' - ',round(time.time() - start_time, 4), "s\n"