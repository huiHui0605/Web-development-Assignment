"""HTTP integration tests against an empty, disposable development database."""
import urllib.request as u, urllib.parse as q, urllib.error, http.cookiejar, re, os
base=os.getenv('BASE_URL','http://127.0.0.1:8080/')
c=u.build_opener(u.HTTPCookieProcessor(http.cookiejar.CookieJar()))
def request(path,data=None):
 try:
  r=c.open(base+path,q.urlencode(data).encode() if data is not None else None); return r.status,r.read().decode()
 except urllib.error.HTTPError as e: return e.code,e.read().decode()
def token(html): return re.search(r'name="csrf" value="([^"]+)"',html)[1]
status,h=request('index.php'); assert status==200 and 'Volunteer Registration Form' in h
assert 'No volunteers registered yet' in request('view.php')[1], 'Use an empty disposable database'
t=token(h)
v=dict(volunteer_name='Test Volunteer',ic_number='001215-14-5678',contact_number='+60 12-345 6789',email='test@example.com',service_category='Environmental Care',available_date='2026-11-22',csrf=t)
assert request('save.php')[0]==405
assert request('save.php',{**v,'csrf':'bad'})[0]==403
for field in ['volunteer_name','ic_number','contact_number','email','service_category','available_date']:
 assert request('save.php',{**v,field:''})[0]==422,field
for field,value in [('available_date','2026-11-\x0022'),('email','bad'),('available_date','2026-02-30'),('service_category','Other'),('ic_number','wrong'),('contact_number','abc'),('volunteer_name','a'*101)]:
 assert request('save.php',{**v,field:value})[0]==422,field
print('PASS: form, method, CSRF, six required fields and invalid values including a null-byte date')
ids=[]
for i in range(51):
 status,h=request('save.php',{**v,'volunteer_name':f'Test Volunteer {i:02}'})
 assert status==200 and 'Volunteer Registered Successfully!' in h
_,h=request('view.php'); ids=re.findall(r'edit.php\?id=(\d+)',h); assert len(ids)==50
assert h.index('Test Volunteer 50')<h.index('Test Volunteer 49')
_,h2=request('view.php?page=2'); older=re.findall(r'edit.php\?id=(\d+)',h2); assert len(older)==1 and 'Test Volunteer 00' in h2
assert 'Previous' in h2 and 'Next' in h and re.search(r'aria-current="page">\s*2', h2)
assert 'Test Volunteer 50' in request('view.php?page=-10')[1]
assert 'Test Volunteer 00' in request('view.php?page=999')[1]
print('PASS: 51 inserts, success details, 50/1 pagination, ordering and page bounds')
id=ids[0]
assert request('edit.php', {**v, 'id':id, 'csrf':'bad'})[0]==403
assert request('edit.php', {**v, 'id':id, 'email':'invalid'})[0]==422
status,h=request('edit.php', {**v,'id':id,'volunteer_name':"<script>alert(1)</script> O'Reilly"})
assert status==200 and '&lt;script&gt;alert(1)&lt;/script&gt;' in h and '<script>alert' not in h
assert 'Volunteer details updated successfully' in h
assert request('edit.php?id=999999')[0]==404
assert request('edit.php?id=invalid')[0]==400
_,h=request('delete.php?id='+id); assert 'Delete registration?' in h
assert request('delete.php',{'id':id,'csrf':'bad'})[0]==403
for id in ids+older:
 status,h=request('delete.php',{'id':id,'csrf':t}); assert status==200 and 'Volunteer registration deleted' in h
assert 'No volunteers registered yet' in h
print('PASS: update persistence, escaped output, invalid IDs, delete confirmation, CSRF and all deletions')
print('ALL CHECKS PASSED; test records removed.')
