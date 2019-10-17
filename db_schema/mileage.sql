/**
 * 마일리지
 */

 CREATE TABLE `imi_mileage` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_mileage.PK',
  `priority` int(11) unsigned NOT NULL COMMENT '마일리지 차감 우선순위 ',
  `charge_taget_name` varchar(50) NOT NULL COMMENT '충전대상',
  `expiration_day` int(11) NOT NULL COMMENT '유효기간',
  `period` varchar(10) DEFAULT NULL COMMENT '주기(''월'',''일'',년'' 선택)',
  `is_out` char(1) NOT NULL DEFAULT 'N' COMMENT '출금여부(default: ''N'', ''Y'')',
  `is_unlimit_period` char(1) DEFAULT 'N' COMMENT '유효기간을 두지 않는 여부(default: ''N'')',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `priority` (`priority`),
  KEY `charge_name` (`charge_taget_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='마일리지 기본정보 테이블';

CREATE TABLE `imi_mileage_change` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_mileage_change PK',
  `member_idx` int(11) unsigned NOT NULL COMMENT 'imi_members.idx FK',
  `mileage_idx` int(11) unsigned NOT NULL COMMENT 'imi_mileage.idx PK',
  `charge_idx` int(11) unsigned NOT NULL COMMENT 'imi_mileage_charge_acount.idx FK',
  `charge_cost` int(11) NOT NULL COMMENT '사용금액',
  `charge_status` int(11) unsigned NOT NULL COMMENT '결제상태',
  `charge_account_no` varchar(100) DEFAULT NULL COMMENT '출금고유번호(핸드폰,상품권 고유번호)',
  `charge_infomation` varchar(80) DEFAULT NULL COMMENT '출금정보(입금은행,통신사 등)',
  `process_date` date NOT NULL COMMENT '처리일자',
  `charge_name` varchar(30) NOT NULL COMMENT '출금자',
  PRIMARY KEY (`idx`),
  KEY `member_idx` (`member_idx`),
  KEY `mileage_idx` (`mileage_idx`),
  KEY `charge_idx` (`charge_idx`),
  KEY `charge_status` (`charge_status`),
  KEY `charge_account_no` (`charge_account_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='마일리지 변동내역 테이블';

CREATE TABLE `imi_mileage_charge` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_mileage_charge.PK',
  `member_idx` int(11) unsigned NOT NULL COMMENT 'imi_members.idx FK',
  `mileage_idx` int(11) unsigned NOT NULL COMMENT 'imi_mileage.idx FK',
  `charge_date` date NOT NULL COMMENT '충전일자',
  `expiration_date` date DEFAULT NULL COMMENT '유효기간 만료일',
  `is_expiration` char(1) NOT NULL DEFAULT 'N' COMMENT '유효기간 만료여부',
  `charge_cost` int(11) NOT NULL COMMENT '충전금액',
  `spare_cost` int(11) NOT NULL COMMENT '남은 마일리지',
  `use_cost` int(11) NOT NULL DEFAULT '0' COMMENT '사용금액',
  `charge_status` int(11) unsigned NOT NULL COMMENT '충전상태',
  `charge_infomation` varchar(80) NOT NULL COMMENT '충전정보(입금은행,통신사 등)',
  `charge_account_no` varchar(255) NOT NULL COMMENT '충전고유번호(핸드폰,상품권,신용카드)',
  `charge_name` varchar(30) NOT NULL COMMENT '충전자',
  PRIMARY KEY (`idx`),
  KEY `expiration_date` (`expiration_date`),
  KEY `fk_mileage_code_charge_status` (`charge_status`),
  KEY `member_idx` (`member_idx`),
  KEY `mileage_idx` (`mileage_idx`),
  KEY `charge_date` (`charge_date`),
  KEY `charge_account_no` (`charge_account_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='마일리지 충전(결제) 내역 테이블';

CREATE TABLE `imi_mileage_code` (
  `mileage_code` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_mileage_change_code PK',
  `mileage_name` varchar(20) NOT NULL COMMENT '마일리지 명칭',
  PRIMARY KEY (`mileage_code`),
  UNIQUE KEY `mileage_name` (`mileage_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='마일리지 코드';

CREATE TABLE `imi_mileage_type_sum` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_mileage_type_sum.PK',
  `member_idx` int(11) unsigned NOT NULL COMMENT 'imi_members.idx FK',
  `virtual_account_sum` int(11) DEFAULT '0' COMMENT '가상계좌 합계',
  `culcture_voucher_sum` int(11) DEFAULT '0' COMMENT '문화상품권 합계',
  `phone_sum` int(11) DEFAULT '0' COMMENT '휴대전화 합계',
  `card_sum` int(11) DEFAULT '0' COMMENT '신용카드 합계',
  `dealings_sum` int(11) DEFAULT '0' COMMENT '거래합계',
  `event_sum` int(11) DEFAULT '0' COMMENT '이벤트 합계',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `member_idx` (`member_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='회원 마일리지별 합계';

CREATE TABLE `imi_sell_item` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_sell_item.PK',
  `item_name` varchar(30) NOT NULL COMMENT '판매물품명',
  `is_sell` char(11) NOT NULL DEFAULT 'Y' COMMENT '판매여부(기본값은 판매함 Y)',
  `commission` tinyint(3) NOT NULL COMMENT '수수료',
  `payback` tinyint(3) NOT NULL COMMENT '페이백',
  PRIMARY KEY (`idx`),
  KEY `item_name` (`item_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='판매물품 정보테이블';

CREATE TABLE `imi_member_virtual_account` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `member_idx` int(11) unsigned NOT NULL COMMENT 'imi_members.idx FK',
  `virtual_account_no` varchar(90) NOT NULL COMMENT '가상계좌번호',
  `bank_name` varchar(30) NOT NULL COMMENT '가상계좌 은행명',
  PRIMARY KEY (`idx`),
  KEY `member_account_idx` (`member_idx`,`bank_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='회원 가상 계좌 정보 테이블';