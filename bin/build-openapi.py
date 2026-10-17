"""Generate the checked-in OpenAPI 3.1 contract; Python has no extra dependencies."""
import json
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
schemas={
 'Club':{'name':{'type':'string','maxLength':120},'sport':{'type':'string','maxLength':100},'description':{'type':'string','maxLength':5000},'coach':{'type':'string','maxLength':100},'location':{'type':'string','maxLength':100},'capacity':{'type':'integer','minimum':1,'maximum':10000}},
 'Tournament':{'name':{'type':'string','maxLength':120},'sport':{'type':'string','maxLength':100},'description':{'type':'string','maxLength':5000},'location':{'type':'string','maxLength':100},'starts_at':{'$ref':'#/components/schemas/LocalDateTime'},'ends_at':{'$ref':'#/components/schemas/LocalDateTime'},'registration_deadline':{'$ref':'#/components/schemas/LocalDateTime'},'capacity':{'type':'integer','minimum':1,'maximum':10000},'status':{'type':'string','enum':['open','ongoing','completed']}},
 'Event':{'title':{'type':'string','maxLength':100},'club_id':{'type':'integer','minimum':1},'tournament_id':{'type':['integer','null'],'minimum':1},'kind':{'type':'string','enum':['training','match']},'location':{'type':'string','maxLength':100},'starts_at':{'$ref':'#/components/schemas/LocalDateTime'},'ends_at':{'$ref':'#/components/schemas/LocalDateTime'},'opponent':{'type':'string','maxLength':120},'home_score':{'type':['integer','null'],'minimum':0,'maximum':100000},'away_score':{'type':['integer','null'],'minimum':0,'maximum':100000}},
 'Resource':{'name':{'type':'string','maxLength':120},'kind':{'type':'string','enum':['facility','equipment']},'description':{'type':'string','maxLength':5000},'location':{'type':'string','maxLength':100}},
 'Registration':{'name':{'type':'string','maxLength':100},'email':{'type':'string','format':'email','maxLength':190},'student_id':{'type':'string','pattern':'^[A-Za-z0-9-]{3,30}$'},'faculty':{'type':'string','maxLength':80},'password':{'type':'string','minLength':12,'maxLength':128}},
 'Login':{'email':{'type':'string','format':'email'},'password':{'type':'string'}},
 'Code':{'code':{'type':'string','description':'Six-digit TOTP code or unused 16-character recovery code.'}},
 'SecondFactor':{'password':{'type':'string'},'code':{'type':'string'}},
 'Booking':{'resource_id':{'type':'integer','minimum':1},'starts_at':{'$ref':'#/components/schemas/LocalDateTime'},'ends_at':{'$ref':'#/components/schemas/LocalDateTime'},'purpose':{'type':'string','maxLength':255}},
 'Review':{'status':{'type':'string','enum':['approved','rejected']}},
 'CommunityPost':{'body':{'type':'string','minLength':1,'maxLength':5000}},
 'CommunityComment':{'body':{'type':'string','minLength':1,'maxLength':2000}},
 'CommunityReport':{'reason':{'type':'string','minLength':1,'maxLength':500}},
 'Moderation':{'status':{'type':'string','enum':['visible','hidden']},'reason':{'type':'string','minLength':1,'maxLength':500}},
 'AttendanceUpdate':{'status':{'type':'string','const':'attended'}},
}
components={name:{'type':'object','properties':props,'required':[key for key in props if not(name=='Event' and key in ['tournament_id','opponent','home_score','away_score'])],'additionalProperties':False} for name,props in schemas.items()}
components.update({
 'Empty':{'type':'object','additionalProperties':False},
 'LocalDateTime':{'type':'string','pattern':r'^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$','description':'Local Asia/Colombo date/time without an offset; for example 2026-12-01T16:00.'},
 'Response':{'type':'object','required':['data','meta'],'properties':{'data':{},'meta':{'type':'object','required':['request_id','csrf_token'],'properties':{'request_id':{'type':'string'},'csrf_token':{'type':'string'},'redirect':{'type':'string'},'pagination':{'type':'object','properties':{'page':{'type':'integer'},'per_page':{'type':'integer'},'total':{'type':'integer'},'pages':{'type':'integer'}}}}}}},
 'Error':{'type':'object','required':['error','meta'],'properties':{'error':{'type':'object','required':['status','message'],'properties':{'status':{'type':'integer'},'message':{'type':'string'}}},'meta':{'type':'object','properties':{'request_id':{'type':'string'},'csrf_token':{'type':['string','null']}}}}},
})
spec={'openapi':'3.1.0','info':{'title':'NSBM SportsHub REST API','version':'1.0.0','description':'Same-origin cookie sessions, JSON requests, CSRF protection, student/admin authorization and TOTP. All local timestamps use Asia/Colombo. PUT replaces an administrative record. No bearer tokens or wildcard CORS.'},'servers':[{'url':'/api/v1'}],'security':[{'sessionCookie':[]}],'paths':{},'components':{'securitySchemes':{'sessionCookie':{'type':'apiKey','in':'cookie','name':'sportshub_session'}},'schemas':components}}
def add(path,method,summary,body=None,public=False,paginated=False,filters=(),created=False):
 op={'summary':summary,'operationId':method+'_'+path.strip('/').replace('/','_').replace('{','').replace('}','').replace('-','_'),'responses':{str(201 if created else 200):{'description':'Success','content':{'application/json':{'schema':{'$ref':'#/components/schemas/Response'}}}}}}
 if public:op['security']=[]
 params=[]
 import re
 for name in re.findall(r'\{([^}]+)\}',path):params.append({'name':name,'in':'path','required':True,'schema':{'type':'integer','minimum':1}})
 if method!='get':params.append({'name':'X-CSRF-Token','in':'header','required':True,'description':'Fetch from auth/session; preserve its session cookie. Token rotates after login/logout.','schema':{'type':'string'}})
 if paginated:
  params.extend([{'name':'page','in':'query','schema':{'type':'integer','minimum':1,'maximum':100000,'default':1}},{'name':'per_page','in':'query','schema':{'type':'integer','minimum':1,'maximum':100,'default':20}}])
 params.extend({'name':name,'in':'query','schema':{'type':'string'}} for name in filters)
 if params:op['parameters']=params
 if body:op['requestBody']={'required':True,'content':{'application/json':{'schema':{'$ref':'#/components/schemas/'+body}}}}
 for code in ['400','401','403','404','405','409','413','415','422','429','500','503']:
  op['responses'][code]={'description':{'400':'Malformed request','401':'Authentication required/invalid','403':'Forbidden or invalid CSRF/origin','404':'Route or record not found','405':'Method not allowed (Allow header)','409':'State conflict','413':'Request too large','415':'JSON content type required','422':'Validation/business-rule failure','429':'Rate limit (Retry-After: 900)','500':'Unexpected server error','503':'Database unavailable'}[code],'content':{'application/json':{'schema':{'$ref':'#/components/schemas/Error'}}}}
 spec['paths'].setdefault(path,{})[method]=op
add('/health','get','Database-backed readiness',public=True)
add('/auth/session','get','Session identity and CSRF bootstrap',public=True)
for path,body,summary in [('register','Registration','Create a student account'),('login','Login','Password login; may return requires_2fa'),('2fa','Code','Complete pending login with a second factor')]:add('/auth/'+path,'post',summary,body,public=True,created=path=='register')
add('/auth/logout','post','End the authenticated session','Empty')
add('/me','get','Current safe user profile')
add('/me/security','get','2FA status and remaining recovery-code count')
add('/me/2fa/setup','post','Generate pending authenticator setup key (10-minute expiry)','Empty')
add('/me/2fa','post','Enroll authenticator and return recovery codes exactly once','SecondFactor')
add('/me/2fa','delete','Disable 2FA with password and second factor','SecondFactor')
for entity,body,filters in [('clubs','Club',['q','sport']),('tournaments','Tournament',['q','sport','status']),('events','Event',['q','kind','club_id','tournament_id']),('resources','Resource',['q','kind'])]:
 add('/'+entity,'get','List '+entity,public=True,paginated=True,filters=filters)
 add('/'+entity+'/{id}','get','Read '+entity+' detail',public=True)
 add('/admin/'+entity,'get','Admin list '+entity,paginated=True)
 add('/admin/'+entity,'post','Create '+entity,body,created=True)
 add('/admin/'+entity+'/{id}','get','Admin read '+entity)
 add('/admin/'+entity+'/{id}','put','Replace '+entity,body)
 add('/admin/'+entity+'/{id}','delete','Delete '+entity+' (linked data may cascade)','Empty')
for path,summary in [('clubs/{id}/memberships','Apply for club membership'),('tournaments/{id}/registrations','Register an individual tournament participant'),('events/{id}/attendance','Register for an approved club session')]:add('/'+path,'post',summary,'Empty',created=True)
add('/bookings','post','Request a future resource reservation (up to four hours)','Booking',created=True)
for entity in ['memberships','registrations','attendance','bookings']:
 add('/me/'+entity,'get','List own '+entity,paginated=True)
 add('/me/'+entity+'/{id}','delete','Cancel own eligible '+entity,'Empty')
for entity in ['memberships','bookings']:
 add('/admin/'+entity,'get','List '+entity+' requests',paginated=True,filters=['status'])
 add('/admin/'+entity+'/{id}','patch','Review pending '+entity,'Review')
add('/admin/attendance','get','Student session registrations',paginated=True)
add('/admin/attendance/{id}','patch','Mark attendance after a session starts','AttendanceUpdate')
add('/admin/reports','get','Club, tournament and booking aggregates')
add('/admin/audit-logs','get','Paginated administration audit history',paginated=True)
add('/results','get','Recorded match scores',public=True,paginated=True,filters=['tournament_id'])
add('/standings','get','Club standings with win=3/draw=1/loss=0',public=True,filters=['tournament_id'])
add('/clubs/{club_id}/community','get','Approved-member club summary')
add('/clubs/{club_id}/members','get','Approved-member roster without contact details',paginated=True)
add('/clubs/{club_id}/posts','get','Visible club feed for approved members',paginated=True)
add('/clubs/{club_id}/posts','post','Share a plain-text post with your club','CommunityPost',created=True)
add('/clubs/{club_id}/posts/{post_id}','get','Read one visible club post')
add('/clubs/{club_id}/posts/{post_id}','patch','Edit your own post; hidden posts stay hidden','CommunityPost')
add('/clubs/{club_id}/posts/{post_id}','delete','Erase your post and its linked comments/reactions/reports','Empty')
add('/clubs/{club_id}/posts/{post_id}/comments','get','Read visible-post comments',paginated=True)
add('/clubs/{club_id}/posts/{post_id}/comments','post','Comment on a club post','CommunityComment',created=True)
add('/clubs/{club_id}/posts/{post_id}/comments/{comment_id}','delete','Remove own comment; administrators may moderate comments','Empty')
add('/clubs/{club_id}/posts/{post_id}/reaction','put','Add one reaction idempotently','Empty')
add('/clubs/{club_id}/posts/{post_id}/reaction','delete','Remove your reaction idempotently','Empty')
add('/clubs/{club_id}/posts/{post_id}/reports','post','Privately report a post once','CommunityReport',created=True)
add('/admin/community','get','Admin-only post moderation queue',paginated=True,filters=['status'])
add('/admin/community/posts/{post_id}/reports','get','Admin-only report details',paginated=True)
add('/admin/community/posts/{post_id}','patch','Hide or restore a post and resolve reports','Moderation')
(ROOT/'public/assets/openapi.json').write_text(json.dumps(spec,indent=2)+'\n')
print('Generated OpenAPI contract:',sum(len(v) for v in spec['paths'].values()),'operations')
