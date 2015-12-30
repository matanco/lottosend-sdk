import json
import urllib2
class LottosendSDK:
	#= Imports	
	
	#= Contrusctor
	def __init__(self):				
		self.token = ''
		self.lottosend_api = ''
		self.results_api = ''
		self.auto_login_url = ''		

	# signup user in lottosend system
	def signupViaApi(self,first_name, last_name, prefix, phone, email, address, country, passwd, a_aid):		
		params = dict()
		params = {
			'web_user': {
				'email': email,
				'first_name': first_name,
				'last_name': last_name,
				'phone': phone,
				'password': passwd,
				'country': country,
				'address': address,
				'aid': a_aid
			}
		}		
		req = urllib2.Request(self.lottosend_api,
            headers = {
                "Authorization": 'Token token=%s' % self.token,
                "Content-Type": "application/json",
                "Accept": "*/*"	            
        	}, data = json.dumps(params))		
		return urllib2.urlopen(req).read()

	# obtain user token to resign-in
	def obtainToken(self,id):
		req = urllib2.Request('%s/%s/token'%(self.lottosend_api,id),
            headers = {
                "Authorization": 'Token token=%s' % self.token,
                "Content-Type": "application/json",
                "Accept": "*/*"	            
        	})

		return urllib2.urlopen(req).read()

	# get all user info
	def getUsersInfo(self):
		req = urllib2.Request('%s/?last_synced_timestamp=1'%self.lottosend_api,
            headers = {
                "Authorization": 'Token token=%s' % self.token,
                "Content-Type": "application/json",
                "Accept": "*/*"	            
        	})
		return urllib2.urlopen(req).read()

	# get user transactions
	def getUsersTransactions(self):
		req = urllib2.Request('%s/transactions/?last_synced_timestamp=1'%self.lottosend_api,
            headers = {
                "Authorization": 'Token token=%s' % self.token,
                "Content-Type": "application/json",
                "Accept": "*/*"	            
        	})
		return urllib2.urlopen(req).read()
