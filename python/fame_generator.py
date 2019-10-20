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

def getExtension(softType):
    return extensionDict.get(str(softType), '.todo')

def getType(softType):
    return typeDict.get(str(softType), 'Unknown')

def dotVersion(version):
	return str(version)+'.'+ str(version % 10)

def save(html, rank, page, curRound):

	if type(page) == int:
		string = str(page)
	else:
		string = 'preview'

	f = open('/var/www/html/fame/'+curRound+'_'+rank+'_'+string+'.html', 'w')
	f.write(html)
	f.close()

def createRankUsers(curRound, preview):

	html = limit = ''
	i = page = 0
	limite = 50

	if preview:
		limit = 'LIMIT 10'

	cur.execute("""	SELECT 
						hist_users.userID, hist_users.reputation, hist_users.bestSoft, hist_users.bestSoftVersion, hist_users.clanName, clan.clanID, 
						users.login, hist_users.rank, hist_users.round
		            FROM hist_users
					LEFT JOIN users
					ON hist_users.userID = users.id
					LEFT JOIN clan
					ON clan.name = hist_users.clanName
					WHERE hist_users.round = %s
					ORDER BY 
						hist_users.round DESC,
						hist_users.rank ASC
					"""+limit+"""
				""", curRound)

	for userID, exp, bestSoft, bestSoftVersion, clanName, clanID, user, rank, histRound in cur.fetchall():

		i += 1
		software = ''

		pos = '<center>'+str(i)+'</center>'

		if clanID:
			clanName = '<a href="clan?id='+str(clanID)+'">'+str(clanName)+'</a>'

		user = '<a href="profile?id='+str(userID)+'">'+str(user)+'</a>'

		power = '<center>'+str(exp)+'</center>'

		if bestSoft:
			software = bestSoft+' <span class="green">('+str(bestSoftVersion)+')</span>'	

		html += '\
                                        <tr>\n\
                                            <td>'+pos+'</td>\n\
                                            <td>'+user+'</td>\n\
                                            <td>'+power+'</td>\n\
                                            <td>'+software+'</td>\n\
                                            <td>'+clanName+'</td>\n\
                                        </tr>\n\
		\n'

		if (i % limite == 0) and not preview:

			save(html, 'user', page, curRound)

			page += 1
			html = ''

	if html != '':

		if preview:
			page = ''

		save(html, 'user', page, curRound)

def createRankClans(curRound, preview):

	html = limit = ''
	i = page = 0
	limite = 50

	if preview:
		limit = 'LIMIT 10'

	cur.execute("""	SELECT 
						clan.clanID, hist_clans.name, hist_clans.nick, hist_clans.reputation, hist_clans.members, hist_clans.won, hist_clans.lost, hist_clans.rate
		            FROM hist_clans
					LEFT JOIN clan
					ON hist_clans.cid = clan.clanID
					WHERE hist_clans.round = %s
					ORDER BY 
						hist_clans.round DESC,
						hist_clans.rank ASC
					"""+limit+"""
				""", curRound)

	for clanID, name, nick, power, members, won, lost, rate in cur.fetchall():

		i += 1

		pos = '<center>'+str(i)+'</center>'

		if rate < 0:
			rate = ''
		else:
			rate = ' <span class="small">'+str(int(rate))+' %</span>'

		clanName = '['+str(nick)+'] '+str(name)

		if clanID:
			clanName = '<a href="clan?id='+str(clanID)+'">'+clanName+'</a>'

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

		if(i % limite == 0) and not preview:

			save(html, 'clan', page, curRound)

			page += 1
			html = ''

	if html != '':

		if preview:
			page = ''

		save(html, 'clan', page, curRound)

def createRankSoft(curRound, preview):

	html = limit = ''
	i = page = 0
	limite = 50

	if preview:
		limit = 'LIMIT 10'

	cur.execute("""	SELECT 
						hist_software.softName, hist_software.softType, hist_software.softVersion, hist_software.owner, hist_software.ownerID
					FROM hist_software
					WHERE 
						hist_software.round = %s AND 
						hist_software.softType != 26
					ORDER BY 
						softVersion DESC,
						softType ASC
					"""+limit+"""
				""", curRound)

	for name, softType, version, owner, ownerID in cur.fetchall():

		i += 1		
		extension = getExtension(softType)

		pos = '<center>'+str(i)+'</center>'
		softName = str(name)+extension
		softVersion = '<center>'+str(version)+'</center>'
		softType = '<a href="?show=software&orderby='+extension+'">'+getType(softType)+'</a>'
		ownerName = '<a href="profile?id='+str(ownerID)+'">'+str(owner)+'</a>'

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

			save(html, 'soft', page, curRound)

			page += 1
			html = ''

	if html != '':

		if preview:
			page = ''

		save(html, 'soft', page, curRound)

def createRankDDoS(curRound, preview):

	html = limit = ''
	i = page = 0
	limite = 50

	if preview:
		limit = 'LIMIT 10'

	cur.execute("""	SELECT 
						hist_ddos.power, hist_ddos.attID, hist_ddos.attUser, hist_ddos.vicID, hist_ddos.servers, hist_ddos.vicUser
					FROM hist_ddos
					WHERE hist_ddos.round = %s
					ORDER BY 
						power DESC,
						servers DESC
					"""+limit+"""
				""", curRound)

	for power, attID, attUser, vicID, servers, victim in cur.fetchall():

		i += 1

		pos = '<center>'+str(i)+'</center>'
		attName = '<a href="profile?id='+str(attID)+'">'+str(attUser)+'</a>'
		vicName = '<a href="profile?id='+str(vicID)+'">'+str(victim)+'</a>'
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

		if(i % limite == 0) and not preview:

			save(html, 'ddos', page, curRound)

			page += 1
			html = ''

	if html != '':

		if preview:
			page = ''

		save(html, 'ddos', page, curRound)	


db = MySQLdb.connect(host="localhost",user="he",passwd="REDADCTED",db="game")
cur = db.cursor()

curRound = str(sys.argv[1])

try:
	preview = sys.argv[2]
	preview = True
except IndexError:
	preview = False

createRankUsers(curRound, preview)

createRankClans(curRound, preview)

createRankSoft(curRound, preview)

createRankDDoS(curRound, preview)