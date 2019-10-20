import MySQLdb
import sys

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


def save(html, rank, page):

	if type(page) != int:
		page = 'preview'

	f = open('/var/www/html/fame/top_'+rank+'_'+str(page)+'.html', 'w')
	f.write(html)
	f.close()

def getExtension(softType):
    return extensionDict.get(str(softType), '.todo')

def getType(softType):
    return typeDict.get(str(softType), 'Unknown')

def dotVersion(version):
	return str(version)+'.'+ str(version % 10)

def createRankUsers(preview):

	html = limit = ''
	i = page = 0
	limite = 50

	if preview:
		limit = 'LIMIT 10'

	cur.execute("""	SELECT 
						hist_users.userID, users.login, 
						clan.clanID, clan.name,
						(
							SUM(hist_users.reputation) + 
							hist_users_current.reputation
						) AS totalReputation, 
						hist_users.bestSoft, hist_users.bestSoftVersion
					FROM hist_users
					LEFT JOIN users 
					ON hist_users.userID = users.id
					LEFT JOIN hist_users_current 
					ON hist_users.userID = hist_users_current.userID
					LEFT JOIN clan_users
					ON hist_users.userID = clan_users.userID
					LEFT JOIN clan
					ON clan.clanID = clan_users.clanID
					GROUP BY hist_users.userID
					ORDER BY 
						totalReputation DESC, 
						hist_users.rank ASC
					"""+limit+"""
				""")

	for userID, username, clanID, clanName, totalReputation, bestSoft, bestSoftVersion in cur.fetchall():

		i += 1
		software = clan = ''

		cur.execute("""	SELECT softName, softVersion 
						FROM software 
						WHERE 
							software.id = 
							(
								SELECT softID
								FROM ranking_software
								WHERE 
									ranking_software.softID IN 
									(
										SELECT software.id
										FROM software
										WHERE software.userID = """+str(userID)+"""
										ORDER BY softVersion DESC
									)
								ORDER BY ranking_software.rank ASC
								LIMIT 1 
							)
						LIMIT 1
					""")

		for curBestSoft, curBestSoftVersion in cur.fetchall():

			if curBestSoftVersion > bestSoftVersion:
				software = curBestSoft+' <span class="green">('+dotVersion(curBestSoftVersion)+')</span>'
			elif bestSoftVersion:
				software = bestSoft+' <span class="green">('+dotVersion(bestSoftVersion)+')</span>'

		if clanID:
			clan = '<a href="clan?id='+str(clanID)+'">'+str(clanName)+'</a>'

		pos = '<center>'+str(i)+'</center>'
		user = '<a href="profile?id='+str(userID)+'">'+str(username)+'</a>'
		power = '<center>'+str(totalReputation)+'</center>'

		if bestSoft:
			software = bestSoft+' <span class="green">('+str(bestSoftVersion)+')</span>'	

		html += '\
                                        <tr>\n\
                                            <td>'+pos+'</td>\n\
                                            <td>'+user+'</td>\n\
                                            <td>'+power+'</td>\n\
                                            <td>'+software+'</td>\n\
                                            <td>'+clan+'</td>\n\
                                        </tr>\n\
		\n'

		if(i % limite == 0) and not preview:

			save(html, 'user', page)

			page += 1
			html = ''

	if html != '':

		if preview:
			page = ''

		save(html, 'user', page)

def createRankClans(preview):

	html = limit = ''
	i = page = 0
	limite = 50

	if preview:
		limit = 'LIMIT 10'

	cur.execute("""	SELECT 
						clan.clanID,
						hist_clans.name, hist_clans.nick, hist_clans_current.members, hist_clans.members,
						(
							SUM(hist_clans.reputation) + 
							hist_clans_current.reputation
						) AS totalReputation,
						(
							SUM(hist_clans.won) + 
							hist_clans_current.won
						) AS totalWon,
						(
							SUM(hist_clans.lost) + 
							hist_clans_current.lost
						) AS totalLost
					FROM hist_clans
					LEFT JOIN hist_clans_current 
					ON hist_clans.cid = hist_clans_current.cid
					LEFT JOIN clan
					ON hist_clans.cid = clan.clanID
					GROUP BY hist_clans.cid
					ORDER BY 
						totalReputation DESC, 
						hist_clans.rank ASC
					"""+limit+"""
				""")

	for clanID, name, nick, membersNow, membersHist, totalPower, won, lost in cur.fetchall():

		i += 1

		pos = '<center>'+str(i)+'</center>'

		if membersNow > membersHist:
			members = membersNow
		else:
			members = membersHist

		if not won:
			won = 0
		if not lost:
			lost = 0

		if won == 0 and lost == 0:
			rate = ''
		else:
			rate = ' <span class="small">'+str(int(round(float(won) / float(lost + won), 2) * 100))+' %</span>'

		clanName = '['+str(nick)+'] '+str(name)

		if clanID:
			clanName = '<a href="clan?id='+str(clanID)+'">'+clanName+'</a>'

		clanPower = '<center>'+str(totalPower)+'</center>'
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

		if(i % limite == 0) and not preview:

			save(html, 'clan', page)

			page += 1
			html = ''

	if html != '':

		if preview:
			page = ''

		save(html, 'clan', page)

def createRankSoft(preview):

	html = limit = ''
	i = page = 0
	limite = 50

	if preview:
		limit = 'LIMIT 10'

	cur.execute("""	SELECT 
						hist_software.softName, hist_software.softType, hist_software.softVersion, hist_software.owner, hist_software.ownerID
					FROM hist_software
					UNION ALL
					(
						SELECT 
							software.softname, software.softType, software.softVersion, '0' AS owner, software.userID AS ownerID
						FROM ranking_software
	                    INNER JOIN software
	                    ON ranking_software.softID = software.id
					)
					ORDER BY 
						softVersion DESC,
						softType ASC
					"""+limit+"""
				""")

	for name, softType, version, owner, ownerID in cur.fetchall():

		i += 1
		extension = getExtension(softType)

		pos = '<center>'+str(i)+'</center>'
		softName = str(name)+extension
		softVersion = '<center>'+str(version)+'</center>'
		softType = '<a href="?show=software&orderby='+extension+'">'+getType(softType)+'</a>'
		ownerName = '<a href="profile?id='+str(ownerID)+'">'+str(owner)+'</a>'

		if owner == '0':
			ownerName = 'Unknown'

		html += '\
                                        <tr>\n\
                                            <td>'+pos+'</td>\n\
                                            <td>'+softName+'</td>\n\
                                            <td>'+softVersion+'</td>\n\
                                            <td>'+ownerName+'</td>\n\
                                            <td>'+softType+'</td>\n\
                                        </tr>\n\
		\n'

		if(i % limite == 0) and not preview:

			save(html, 'soft', page)

			page += 1
			html = ''

	if html != '':

		if preview:
			page = ''

		save(html, 'soft', page)

def createRankDDoS(preview):

	html = limit = ''
	i = page = 0
	limite = 50

	if preview:
		limit = 'LIMIT 10'

	cur.execute("""	SELECT 
						hist_ddos.power, hist_ddos.attID, hist_ddos.attUser, hist_ddos.vicID, hist_ddos.servers, hist_ddos.vicUser AS victim
					FROM hist_ddos
					UNION ALL
					(
						SELECT 
							round_ddos.power, round_ddos.attID, round_ddos.attUser, round_ddos.vicID, round_ddos.servers, round_ddos.vicNPC AS victim
						FROM round_ddos
					)
					ORDER BY 
						power DESC,
						servers DESC
					"""+limit+"""
				""")

	for power, attID, attUser, vicID, servers, victim in cur.fetchall():

		i += 1

		pos = '<center>'+str(i)+'</center>'
		attName = '<a href="profile?id='+str(attID)+'">'+str(attUser)+'</a>'
		power = '<center>'+str(power)+'</center>'
		servers = '<center>'+str(servers)+'</center>'

		if victim != '0':
			vicName = '<a href="profile?id='+str(vicID)+'">'+str(victim)+'</a>'
		else:
			vicName = 'Unknown'

		html += '\
                                        <tr>\n\
                                            <td>'+pos+'</td>\n\
                                            <td>'+attName+'</td>\n\
                                            <td>'+vicName+'</td>\n\
                                            <td>'+power+'</td>\n\
                                            <td>'+servers+'</td>\n\
                                        </tr>\n\
		\n'

		if(i % limite == 0) and not preview:

			save(html, 'ddos', page)

			page += 1
			html = ''

	if html != '':

		if preview:
			page = ''

		save(html, 'ddos', page)

db = MySQLdb.connect(host="localhost",user="he",passwd="REDADCTED",db="game")
cur = db.cursor()

try:
	preview = sys.argv[1]
	preview = True
except IndexError:
	preview = False

createRankUsers(preview)

createRankClans(preview)

createRankSoft(preview)

createRankDDoS(preview)