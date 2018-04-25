delete  from  roleinfo  t where t.roleserial=14;
insert into roleinfo (ROLESERIAL, ROLENAME, ISENABLE, PRIVILEGE, CREATOR, REMARK, CREATETIME)
values (14, '顶级代理商管理员', 0, 'FFFFFFFFFFFFFFFFFFFFFFFFFF', null, '顶级代理商', to_date('02-06-2017 09:34:56', 'dd-mm-yyyy hh24:mi:ss'));

commit;
