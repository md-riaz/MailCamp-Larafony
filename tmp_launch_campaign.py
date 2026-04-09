import requests
base='https://mailcamp.opc.mdriaz.com.bd'
s=requests.Session()
r=s.post(base+'/login',data={'email':'admin@example.com','password':'password'},allow_redirects=True,timeout=30)
print('login', r.status_code, r.url)
r=s.post(base+'/campaigns/9/launch',allow_redirects=False,timeout=60)
print('status', r.status_code)
print('location', r.headers.get('location'))
print(r.text[:2000])
