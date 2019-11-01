/**
 * 거래
 */

CREATE TABLE `th_dealings` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_dealings.PK',
  `dealings_type` enum('구매','판매') NOT NULL COMMENT 'th_dealings_code.idx FK 거래종류(판매/구매) ',
  `register_date` date NOT NULL COMMENT '거래등록일시',
  `expiration_date` date NOT NULL COMMENT '거래글 삭제일',
  `dealings_subject` varchar(80) NOT NULL COMMENT '거래제목',
  `dealings_content` text NOT NULL COMMENT '거래내용',
  `writer_idx` int(11) unsigned NOT NULL COMMENT '거래작성자 고유번호 th_members.idx',
  `item_no` int(11) unsigned NOT NULL COMMENT 'th_sell_item.idx FK',
  `item_money` int(11) NOT NULL COMMENT '판매물품금액',
  `item_object_no` varchar(150) DEFAULT NULL COMMENT '거래물품의 고유번호',
  `dealings_mileage` int(11) NOT NULL COMMENT '실제 거래하는 마일리지 ',
  `dealings_commission` int(11) NOT NULL COMMENT '거래 시 발생하는 수수료',
  `dealings_status` int(11) unsigned NOT NULL COMMENT '거래 처리상태 th_dealings_status_code.idx FK',
  `is_del` char(1) NOT NULL DEFAULT 'N' COMMENT '거래게시글 삭제여부(디폴트는 삭제안함)',
  `memo` varchar(100) DEFAULT NULL COMMENT '비고 ',
  PRIMARY KEY (`idx`),
  KEY `dealings_status` (`dealings_status`),
  KEY `expiration_date` (`expiration_date`),
  KEY `register_date` (`register_date`),
  KEY `dealings_writer_no` (`writer_idx`),
  KEY `sell_item_no` (`item_no`),
  KEY `dealings_object_no` (`item_object_no`),
  KEY `dealings_subject` (`dealings_subject`),
  KEY `dealings_type` (`dealings_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='판매와 구매를 등록 할 수 있는 거래 테이블';

CREATE TABLE `th_dealings_commission` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_dealings_commission.idx PK',
  `dealings_idx` int(11) unsigned NOT NULL COMMENT 'th_dealings.idx FK',
  `commission` int(11) NOT NULL COMMENT '수수료',
  `dealings_complete_date` date NOT NULL COMMENT '최종거래일자',
  `sell_item_idx` int(11) unsigned NOT NULL COMMENT 'th_sell_item.idx FK',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `dealings_idx` (`dealings_idx`),
  KEY `sell_item_idx` (`sell_item_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='거래 수수료 테이블';

CREATE TABLE `th_dealings_mileage_change` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_dealings_mileage_change.idx PK',
  `dealings_idx` int(11) unsigned NOT NULL COMMENT 'th_dealings.idx PK',
  `dealings_writer_idx` int(11) unsigned NOT NULL COMMENT '거래글 작성자(th_members.idx)',
  `dealings_member_idx` int(11) unsigned NOT NULL COMMENT '거래를 하는 사람(th_members.idx)',
  `charge_idx` int(11) unsigned NOT NULL COMMENT 'th_mileage_charge.idx FK',
  `dealings_status_code` int(11) unsigned NOT NULL COMMENT 'th_dealings_status_code.idx FK',
  `dealings_date` date NOT NULL COMMENT '마일리지를 결제한 날짜',
  `refund_date` date DEFAULT NULL COMMENT '환불날짜',
  `dealings_money` int(11) NOT NULL COMMENT '거래금액',
  `memo` varchar(180) DEFAULT NULL COMMENT '비고',
  PRIMARY KEY (`idx`),
  KEY `dealings_idx` (`dealings_idx`),
  KEY `dealings_writer_idx` (`dealings_writer_idx`),
  KEY `dealings_member_idx` (`dealings_member_idx`),
  KEY `dealings_status_code` (`dealings_status_code`),
  KEY `dealing_date` (`dealings_date`),
  KEY `charge_idx` (`charge_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='거래시 마일리지 변동 내역';

CREATE TABLE `th_dealings_process` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_dealings_process.idx PK',
  `dealings_idx` int(11) unsigned NOT NULL COMMENT 'th_dealings.idx FK',
  `dealings_status_idx` int(11) unsigned NOT NULL COMMENT 'th_dealings_status_code.idx FK',
  `dealings_datetime` datetime NOT NULL COMMENT '거래상태 변경시각',
  PRIMARY KEY (`idx`),
  KEY `dealings_idx` (`dealings_idx`),
  KEY `dealings_status_idx` (`dealings_status_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='거래 처리과정 테이블';

CREATE TABLE `th_dealings_status_code` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_dealings_status_code.PK idx',
  `dealing_status_order` int(11) unsigned NOT NULL COMMENT '거래 상태 순서',
  `dealings_status_name` varchar(30) NOT NULL COMMENT '거래상태명',
  `process_type` varchar(10) NOT NULL DEFAULT '거래' COMMENT '진행과정타입(거래/비거래)',
  PRIMARY KEY (`idx`),
  KEY `dealing_status_name` (`dealings_status_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='거래 상태 코드 테이블';

CREATE TABLE `th_dealings_user` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_dealings_user.idx PK',
  `dealings_type` enum('구매','판매') NOT NULL COMMENT '거래종류(구매/판매)',
  `dealings_idx` int(11) unsigned NOT NULL COMMENT 'th_dealings.idx FK',
  `dealings_writer_idx` int(11) unsigned NOT NULL COMMENT 'imi.members.idx FK 거래글 작성자 ',
  `dealings_member_idx` int(11) unsigned NOT NULL COMMENT 'imi.members.idx FK 거래하는 사람',
  `dealings_status` int(11) unsigned NOT NULL COMMENT 'th_dealings_status_code.idx 거래상태 ',
  `dealings_date` date NOT NULL COMMENT '거래일자',
  PRIMARY KEY (`idx`),
  KEY `dealings_idx` (`dealings_idx`),
  KEY `dealings_writer_idx` (`dealings_writer_idx`),
  KEY `dealings_member_idx` (`dealings_member_idx`),
  KEY `dealings_status` (`dealings_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='거래 유저 정보';

