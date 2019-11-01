/**
 * 관리자
 */

CREATE TABLE `th_admin` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_members.pk',
  `id` varchar(50) NOT NULL COMMENT '아이디',
  `password` varchar(100) NOT NULL COMMENT '패스워드',
  `email` varchar(70) NOT NULL COMMENT '이메일',
  `name` varchar(40) NOT NULL COMMENT '이름',
  `phone` varchar(40) NOT NULL COMMENT '연락처',
  `sex` char(1) NOT NULL DEFAULT 'M' COMMENT '성별(M:남자, W:여자)',
  `birth` varchar(40) NOT NULL COMMENT '생년월일',
  `join_date` date NOT NULL COMMENT '최초가입일자',
  `join_approval_date` date DEFAULT NULL COMMENT '메일승인일자',
  `withdraw_date` date DEFAULT NULL COMMENT '탈퇴일',
  `forcedEviction_date` date DEFAULT NULL COMMENT '강제탈퇴일',
  `is_forcedEviction` char(1) DEFAULT 'N' COMMENT '강제탈퇴여부',
  `modify_date` date DEFAULT NULL COMMENT '회원정보 수정일자',
  `is_superadmin` char(1) NOT NULL DEFAULT 'N' COMMENT '슈퍼관리자여부',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`),
  KEY `name` (`name`),
  KEY `birth` (`birth`),
  KEY `join_approval_date` (`join_approval_date`),
  KEY `withdraw_date` (`withdraw_date`),
  KEY `forcedEviction_date` (`forcedEviction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='아이엠아이 관리자 테이블 ';

CREATE TABLE `th_admin_access_ip` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_access_ip.PK',
  `admin_idx` int(11) unsigned NOT NULL COMMENT 'th_member.idx PK',
  `access_ip` varchar(70) NOT NULL COMMENT '아이피',
  `access_date` date NOT NULL COMMENT '접근일자',
  `access_datetime` datetime NOT NULL COMMENT '접근시각',
  `access_user_agent` varchar(255) NOT NULL COMMENT '접근브라우저',
  PRIMARY KEY (`idx`),
  KEY `admin_idx` (`admin_idx`,`access_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='관리자 접속 내역';