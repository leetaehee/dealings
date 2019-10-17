/**
 * 회원
 */

CREATE TABLE `imi_members` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_members.pk',
  `id` varchar(50) NOT NULL COMMENT '아이디',
  `grade_code` int(11) unsigned NOT NULL COMMENT '일반회원 등급',
  `password` varchar(255) NOT NULL COMMENT '패스워드',
  `email` varchar(70) NOT NULL COMMENT '이메일',
  `name` varchar(40) NOT NULL COMMENT '이름',
  `phone` varchar(40) NOT NULL COMMENT '연락처',
  `sex` char(1) NOT NULL DEFAULT 'M' COMMENT '성별(M:남자, W:여자)',
  `birth` varchar(70) NOT NULL COMMENT '생년월일',
  `join_date` date NOT NULL COMMENT '최초가입일자',
  `join_approval_date` date DEFAULT NULL COMMENT '가입승인일자',
  `withdraw_date` date DEFAULT NULL COMMENT '탈퇴일',
  `modify_date` date DEFAULT NULL COMMENT '회원정보 수정일자',
  `forcedEviction_date` date DEFAULT NULL COMMENT '강제탈퇴일자',
  `is_forcedEviction` char(1) DEFAULT 'N' COMMENT '강제탈퇴여부 : 강제탈퇴가 될 경우 ''Y''로 변경 후 등급 컬럼은 가장 하위 등급으로 할 것.',
  `mileage` int(11) NOT NULL DEFAULT '0' COMMENT '회원 마일리지',
  `point` int(11) NOT NULL DEFAULT '0' COMMENT '포인트',
  `account_no` varchar(150) DEFAULT NULL COMMENT '계좌번호',
  `account_bank` varchar(20) DEFAULT NULL COMMENT '계좌은행명',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `phone` (`phone`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE,
  UNIQUE KEY `id` (`id`) USING BTREE,
  KEY `join_approval_date` (`join_approval_date`),
  KEY `withdraw_date` (`withdraw_date`),
  KEY `birth` (`birth`),
  KEY `grade_code` (`grade_code`),
  KEY `name` (`name`),
  KEY `forcedEviction_date` (`forcedEviction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='아이엠아이 회원 관리 테이블 ';

CREATE TABLE `imi_member_grades` (
  `grade_code` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_member_grades.PK',
  `grade_order` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '등급순서(가장 낮은 회원은 1번이어함)',
  `grade_name` varchar(30) DEFAULT NULL COMMENT '등급명',
  `taget_point` int(11) DEFAULT '0' COMMENT '목표 포인트',
  PRIMARY KEY (`grade_code`),
  KEY `grade_name` (`grade_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='회원 등급 테이블';

CREATE TABLE `imi_member_activity_history` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_member_activity_history.idx PK',
  `member_idx` int(11) unsigned NOT NULL COMMENT 'imi_member.idx PK',
  `grade_code` int(11) unsigned DEFAULT NULL COMMENT 'imi_member_grades.grade_code FK',
  `change_datetime` datetime DEFAULT NULL COMMENT '변동시각\n',
  `change_memo` text COMMENT '변동사유',
  PRIMARY KEY (`idx`),
  KEY `member_idx` (`member_idx`,`grade_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='회원 활동 이력 테이블';

CREATE TABLE `imi_activity_point_type` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_activity_point_type.PK',
  `activity_type` varchar(30) NOT NULL COMMENT '활동유형',
  `point` int(11) NOT NULL COMMENT '부여되는 포인트',
  PRIMARY KEY (`idx`),
  KEY `activity_type` (`activity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='활동 포인트 유형';

CREATE TABLE `imi_access_ip` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_access_ip.PK',
  `member_idx` int(11) unsigned NOT NULL COMMENT 'imi_member.idx PK',
  `access_ip` varchar(70) NOT NULL COMMENT '아이피',
  `access_date` date NOT NULL COMMENT '접근일자',
  `access_datetime` datetime NOT NULL COMMENT '접근시각',
  `access_user_agent` varchar(255) NOT NULL COMMENT '접근브라우저',
  PRIMARY KEY (`idx`),
  KEY `member_idx` (`member_idx`,`access_date`)
) ENGINE=InnoDB EFAULT CHARSET=utf8 COMMENT='일반회원 접속 내역';



