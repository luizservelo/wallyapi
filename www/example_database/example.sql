create table example_users(
    user_id varchar(36) primary key,
    user_name varchar(255) not null,
    user_email varchar(255) not null unique,
    user_password varchar(255) not null,
    created_at datetime default current_timestamp,
    updated_at datetime default current_timestamp on update current_timestamp
);
