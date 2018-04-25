delete  from  loginaccount  t where t.merchantid='999999';
insert into loginaccount (MERCHANTID, LOGINCODE, LOGINPWD, USERTYPE, ISENABLE, STATUS, TELNO, DEPARTSERIAL, ROLESERIAL, STORESERIAL, CREATOR, ONLINESTATUS, LOGINTIME, LOGOUTTIME, LOGINIP, LOGINADDR, CREATETIME, UPDATETIME)
values ('999999', '999999', 'E10ADC3949BA59ABBE56E057F20F883E', 0, 0, 1, '15083905145', null, '14', '999999', '999999', 1, to_date('10-08-2017 13:46:27', 'dd-mm-yyyy hh24:mi:ss'), to_date('10-08-2017 13:06:59', 'dd-mm-yyyy hh24:mi:ss'), '61.141.137.151', '广东省深圳市', to_date('09-05-2017', 'dd-mm-yyyy'), to_date('10-08-2017 13:46:27', 'dd-mm-yyyy hh24:mi:ss'));

commit;
