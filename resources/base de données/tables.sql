create table answer
(
    id              int auto_increment
        primary key,
    student_id      int not null,
    question_id     int not null,
    choice_id       int not null,
    quiz_attempt_id int not null,
    constraint FK_DADD4A251E27F6BF
        foreign key (question_id) references question (id),
    constraint FK_DADD4A25998666D1
        foreign key (choice_id) references choice (id),
    constraint FK_DADD4A25CB944F1A
        foreign key (student_id) references member (id),
    constraint FK_DADD4A25F8FE9957
        foreign key (quiz_attempt_id) references quiz_attempt (id)
)
    collate = utf8_unicode_ci;

create index IDX_DADD4A251E27F6BF
    on answer (question_id);

create index IDX_DADD4A25998666D1
    on answer (choice_id);

create index IDX_DADD4A25CB944F1A
    on answer (student_id);

create index IDX_DADD4A25F8FE9957
    on answer (quiz_attempt_id);


create table choice
(
    id          int auto_increment
        primary key,
    question_id int          not null,
    content     varchar(255) not null,
    is_answer   tinyint(1)   not null,
    constraint FK_C1AB5A921E27F6BF
        foreign key (question_id) references question (id)
)
    collate = utf8_unicode_ci;

create index IDX_C1AB5A921E27F6BF
    on choice (question_id);


create table course
(
    id            int auto_increment
        primary key,
    teacher       int          null,
    level         int          null,
    subject       int          null,
    name          varchar(255) not null,
    description   longtext     not null,
    is_valid      tinyint(1)   null,
    content       longtext     not null,
    date_creation datetime     not null,
    date_update   datetime     not null,
    constraint FK_169E6FB99AEACC13
        foreign key (level) references level (id),
    constraint FK_169E6FB9B0F6A6D5
        foreign key (teacher) references member (id),
    constraint FK_169E6FB9FBCE3E7A
        foreign key (subject) references subject (id)
)
    collate = utf8_unicode_ci;

create index IDX_169E6FB99AEACC13
    on course (level);

create index IDX_169E6FB9B0F6A6D5
    on course (teacher);

create index IDX_169E6FB9FBCE3E7A
    on course (subject);


create table desktop_document
(
    id         int auto_increment
        primary key,
    student_id int          not null,
    name       varchar(255) not null,
    constraint FK_DCF040BCB944F1A
        foreign key (student_id) references member (id)
)
    collate = utf8_unicode_ci;

create index IDX_DCF040BCB944F1A
    on desktop_document (student_id);


create table document
(
    id          int auto_increment
        primary key,
    course_id   int          not null,
    name        varchar(150) not null,
    description varchar(255) not null,
    constraint FK_D8698A76591CC992
        foreign key (course_id) references course (id)
)
    collate = utf8_unicode_ci;

create index IDX_D8698A76591CC992
    on document (course_id);


create table event
(
    id         int auto_increment
        primary key,
    student_id int          not null,
    title      varchar(255) not null,
    url        longtext     null,
    class      varchar(255) not null,
    content    longtext     null,
    start_date datetime     not null,
    end_date   datetime     null,
    constraint FK_3BAE0AA7CB944F1A
        foreign key (student_id) references member (id)
)
    collate = utf8_unicode_ci;

create index IDX_3BAE0AA7CB944F1A
    on event (student_id);


create table exam
(
    id         int auto_increment
        primary key,
    course_id  int      not null,
    content    longtext not null,
    correction longtext not null,
    constraint FK_38BBA6C6591CC992
        foreign key (course_id) references course (id)
)
    collate = utf8_unicode_ci;

create index IDX_38BBA6C6591CC992
    on exam (course_id);


create table exercise
(
    id         int auto_increment
        primary key,
    course_id  int      not null,
    content    longtext not null,
    correction longtext not null,
    constraint FK_AEDAD51C591CC992
        foreign key (course_id) references course (id)
)
    collate = utf8_unicode_ci;

create index IDX_AEDAD51C591CC992
    on exercise (course_id);


create table forum
(
    id    int auto_increment
        primary key,
    level int not null,
    constraint UNIQ_852BBECD9AEACC13
        unique (level),
    constraint FK_852BBECD9AEACC13
        foreign key (level) references level (id)
)
    collate = utf8_unicode_ci;


create table historic_action
(
    id          int auto_increment
        primary key,
    actor_id    int          not null,
    description varchar(255) not null,
    date        datetime     not null,
    link        varchar(500) null,
    constraint FK_E43A43B810DAF24A
        foreign key (actor_id) references member (id)
)
    collate = utf8_unicode_ci;

create index IDX_E43A43B810DAF24A
    on historic_action (actor_id);


create table kinship
(
    id         int auto_increment
        primary key,
    tutor_id   int not null,
    student_id int not null,
    constraint UNIQ_C65064E7CB944F1A
        unique (student_id),
    constraint FK_C65064E7208F64F1
        foreign key (tutor_id) references member (id),
    constraint FK_C65064E7CB944F1A
        foreign key (student_id) references member (id)
)
    collate = utf8_unicode_ci;

create index IDX_C65064E7208F64F1
    on kinship (tutor_id);


create table level
(
    id   int auto_increment
        primary key,
    name varchar(150) not null
)
    collate = utf8_unicode_ci;


create table level_subject
(
    level_id   int not null,
    subject_id int not null,
    primary key (level_id, subject_id),
    constraint FK_5A661B6523EDC87
        foreign key (subject_id) references subject (id)
            on delete cascade,
    constraint FK_5A661B655FB14BA7
        foreign key (level_id) references level (id)
            on delete cascade
)
    collate = utf8_unicode_ci;

create index IDX_5A661B6523EDC87
    on level_subject (subject_id);

create index IDX_5A661B655FB14BA7
    on level_subject (level_id);


create table member
(
    id                int auto_increment
        primary key,
    kinship_tutor_id  int          null,
    email             varchar(180) not null,
    roles             json         not null,
    password          varchar(255) not null,
    first_name        varchar(255) not null,
    last_name         varchar(255) not null,
    is_active         tinyint(1)   not null,
    date_registration datetime     not null,
    date_birth        date         not null,
    constraint UNIQ_70E4FA78E0DD77EC
        unique (kinship_tutor_id),
    constraint UNIQ_70E4FA78E7927C74
        unique (email),
    constraint FK_70E4FA78E0DD77EC
        foreign key (kinship_tutor_id) references kinship (id)
)
    collate = utf8_unicode_ci;


create table migration_versions
(
    version     varchar(14) not null
        primary key,
    executed_at datetime    not null comment '(DC2Type:datetime_immutable)'
)
    collate = utf8_unicode_ci;

INSERT INTO ter.migration_versions (version, executed_at) VALUES ('20200425130747', '2020-04-25 13:08:00');
create table post
(
    id      int auto_increment
        primary key,
    author  int      not null,
    topic   int      not null,
    content longtext not null,
    date    datetime not null,
    constraint FK_5A8A6C8D9D40DE1B
        foreign key (topic) references topic (id),
    constraint FK_5A8A6C8DBDAFD8C8
        foreign key (author) references member (id)
)
    collate = utf8_unicode_ci;

create index IDX_5A8A6C8D9D40DE1B
    on post (topic);

create index IDX_5A8A6C8DBDAFD8C8
    on post (author);


create table question
(
    id      int auto_increment
        primary key,
    quiz_id int          not null,
    content varchar(255) not null,
    constraint FK_B6F7494E853CD175
        foreign key (quiz_id) references quiz (id)
)
    collate = utf8_unicode_ci;

create index IDX_B6F7494E853CD175
    on question (quiz_id);


create table quiz
(
    id        int auto_increment
        primary key,
    course_id int not null,
    constraint FK_A412FA92591CC992
        foreign key (course_id) references course (id)
)
    collate = utf8_unicode_ci;

create index IDX_A412FA92591CC992
    on quiz (course_id);


create table quiz_attempt
(
    id          int auto_increment
        primary key,
    student_id  int        not null,
    quiz_id     int        not null,
    date        datetime   not null,
    score       double     not null,
    is_complete tinyint(1) not null,
    constraint FK_AB6AFC6853CD175
        foreign key (quiz_id) references quiz (id),
    constraint FK_AB6AFC6CB944F1A
        foreign key (student_id) references member (id)
)
    collate = utf8_unicode_ci;

create index IDX_AB6AFC6853CD175
    on quiz_attempt (quiz_id);

create index IDX_AB6AFC6CB944F1A
    on quiz_attempt (student_id);


create table subject
(
    id   int auto_increment
        primary key,
    name varchar(150) not null
)
    collate = utf8_unicode_ci;


create table subscription
(
    id         int auto_increment
        primary key,
    student    int        not null,
    level      int        not null,
    date_start datetime   not null,
    date_end   datetime   not null,
    is_active  tinyint(1) not null,
    price      double     not null,
    constraint FK_A3C664D39AEACC13
        foreign key (level) references level (id),
    constraint FK_A3C664D3B723AF33
        foreign key (student) references member (id)
)
    collate = utf8_unicode_ci;

create index IDX_A3C664D39AEACC13
    on subscription (level);

create index IDX_A3C664D3B723AF33
    on subscription (student);


create table subscription_subject
(
    subscription_id int not null,
    subject_id      int not null,
    primary key (subscription_id, subject_id),
    constraint FK_88DBDEE123EDC87
        foreign key (subject_id) references subject (id)
            on delete cascade,
    constraint FK_88DBDEE19A1887DC
        foreign key (subscription_id) references subscription (id)
            on delete cascade
)
    collate = utf8_unicode_ci;

create index IDX_88DBDEE123EDC87
    on subscription_subject (subject_id);

create index IDX_88DBDEE19A1887DC
    on subscription_subject (subscription_id);


create table topic
(
    id      int auto_increment
        primary key,
    subject int          not null,
    author  int          not null,
    forum   int          not null,
    title   varchar(255) not null,
    content longtext     not null,
    date    datetime     not null,
    constraint FK_9D40DE1B852BBECD
        foreign key (forum) references forum (id),
    constraint FK_9D40DE1BBDAFD8C8
        foreign key (author) references member (id),
    constraint FK_9D40DE1BFBCE3E7A
        foreign key (subject) references subject (id)
)
    collate = utf8_unicode_ci;

create index IDX_9D40DE1B852BBECD
    on topic (forum);

create index IDX_9D40DE1BBDAFD8C8
    on topic (author);

create index IDX_9D40DE1BFBCE3E7A
    on topic (subject);


create table video
(
    id          int auto_increment
        primary key,
    course_id   int          not null,
    name        varchar(150) not null,
    description varchar(255) not null,
    constraint FK_7CC7DA2C591CC992
        foreign key (course_id) references course (id)
)
    collate = utf8_unicode_ci;

create index IDX_7CC7DA2C591CC992
    on video (course_id);

