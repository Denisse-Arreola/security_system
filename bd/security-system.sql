# Creación de la base de datos
create database security_system;
use security_system;

# Creación de tablas
create table laboratory
(	lab_id int auto_increment not null,
	lab_name varchar(50) null,
    
    constraint PK_lab_id primary key (lab_id)
);

create table employees
(	emp_id bigint not null,
	emp_firstname varchar(60) not null,
    emp_lastname varchar(60) not null,
    emp_pass varchar(30) not null,
    FK_lab int not null,
    
    constraint PK_emp_id primary key (emp_id),
    constraint FK_emp_lab foreign key (FK_lab) references laboratory(lab_id) on delete cascade
);

create table lab_alarm
(	la_id int auto_increment not null,
	la_status boolean not null default false,
    FK_lab int not null,
    
    constraint PK_alarm_id primary key (la_id),
    constraint FK_alarm_lab foreign key (FK_lab) references laboratory(lab_id) on delete cascade
);

create table lab_status
(	ls_id int auto_increment not null,
	ls_temperature float not null,
    ls_humidity float not null,
    ls_motion boolean not null,			
    ls_alarm_run boolean not null,		
	ls_dateTime datetime not null default current_timestamp on update current_timestamp,
    FK_lab int not null,
    
    constraint PK_status_id primary key (ls_id),
    constraint FK_lab_status foreign key (FK_lab) references laboratory(lab_id) on delete cascade
);

# Creación de procedimientos almacenados
DELIMITER //
create procedure sp_insert_lab
(	
	in labName varchar(50)
)
begin 
	insert into laboratory (lab_name) values(labName);
    set @lab = (select max(lab_id) from laboratory);
    insert into lab_alarm (FK_lab) values(@lab);

end //
DELIMITER ;

DELIMITER //
create procedure sp_insert_employee
(	in id bigint,
	in firstname varchar(60),
    in lastname varchar(60),
    in pass varchar(30),
    in labKey int
)
begin 
	declare lab int;
    set lab = (select lab_id from laboratory where lab_access = labKey);

	insert into employees
    (emp_id, emp_firstname, emp_lastname, emp_pass, FK_lab) 
    values(id, firstname, lastname, pass, lab);

end //
DELIMITER ;

DELIMITER //
create procedure sp_insert_status
(	in temperature float,
	in humidity float,
    in motion  boolean,
    in alarm_running boolean,
    in lab int
)
begin 
	insert into lab_status
    (ls_temperature, ls_humidity, ls_motion, ls_alarm_run, FK_lab) 
    values(temperature, humidity, motion, alarm_running, lab);

end //
DELIMITER ;

DELIMITER //
create procedure sp_turn_alarm
(	in lab int,
	in aStatus boolean
)
begin 
	update lab_alarm
    set la_status = aStatus
    where FK_lab = lab;

end //
DELIMITER ;

# Creación de vistas
create view vw_employee as
select emp_id as id, emp_firstname as firstname, 
emp_lastname as lastname, emp_pass as pass, FK_lab as laboratory 
from employees;

create view vw_alarm_status as
select la_status as alarm, FK_lab as laboratory
from lab_alarm;


create view vw_laboratory as
	select 
		lab_id as id, 
        lab_name as laboratory,
        la_id as alarm,
        la_status as alarm_status,
        ls_temperature as temperature,
        ls_humidity as humidity,
        ls_motion as motion,
        ls_alarm_run as alarm_running,
        ls_dateTime as date_time
	from 
		laboratory as lab
			inner join 
		lab_alarm as al on al.FK_lab = lab.lab_id
			inner join 
		lab_status as st on st.FK_lab = lab.lab_id
	order by date_time desc
	limit 1;
    
create view vw_motion as
	select 
		ls_id as num,
		lab_id as id,
		lab_name as laboratory,
		ls_motion as motion, 
		date_format(ls_dateTime, "%H:%i") as time 
	from lab_status inner join laboratory 
    on FK_lab = lab_id;
    

    
create view vw_laboratory_data as
	select 
		lab_id as id, 
        lab_name as laboratory,
        la_id as alarm,
        la_status as alarm_status,
        ls_id as counter,
        ls_temperature as temperature,
        ls_humidity as humidity,
        ls_motion as motion,
        ls_alarm_run as alarm_running,
        ls_dateTime as date_time
	from 
		laboratory as lab
			inner join 
		lab_alarm as al on al.FK_lab = lab.lab_id
			inner join 
		lab_status as st on st.FK_lab = lab.lab_id;

create view vw_laboratory_data_min as
select 
	id, 
    laboratory, 
    avg(temperature) as temperature, 
    avg(humidity) as humidity, 
    sum(motion) as motion, 
    sum(alarm_running) as ar, 
    date_time,
    date(date_time) as date,
    concat(hour(date_time), ":00") as time 
from vw_laboratory_data
group by time, day(date_time);

create view vw_laboratory_data_week as
select 
		lab_id as id, 
        lab_name as laboratory,
        ls_temperature as temperature,
        ls_humidity as humidity,
        sum(ls_motion) as motion,
        ls_alarm_run as alarm_running,
        ls_dateTime as date_time,
        "---" as time,
        date_format(ls_dateTime, "%W") as date
	from 
		laboratory as lab
			inner join 
		lab_status as st on st.FK_lab = lab.lab_id group by date;

create view vw_laboratory_alarm_motion as
select 
		ls_id as counter,
		lab_id as id, 
        lab_name as laboratory,
        ls_motion as motion,
        ls_alarm_run as alarm_running,
        date_format(ls_dateTime, "%d - %m - %Y") as date,
        date_format(ls_dateTime, "%H:%i") as time
	from 
		laboratory as lab
			inner join 
		lab_status as st on st.FK_lab = lab.lab_id;



# Inserts incluidos en la base de datos al iniciar el programa
call sp_insert_lab("MONITORING");
call sp_insert_employee(319124849, "MARCUS", "HERNANDEZ", "security_system", 1);
call sp_turn_alarm(1, false);
call sp_turn_alarm(1, 0);
