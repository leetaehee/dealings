/**
 * 쿠폰
 */

CREATE TABLE `th_coupon` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_coupon.idx PK',
  `issue_type` enum('구매','판매') NOT NULL COMMENT '쿠폰발행타입',
  `sell_item_idx` int(11) unsigned NOT NULL COMMENT 'th_sell_item.idx FK',
  `subject` varchar(255) NOT NULL COMMENT '쿠폰 이름',
  `item_money` mediumint(6) NOT NULL COMMENT '상품권 및 수수료 금액',
  `discount_rate` tinyint(3) NOT NULL COMMENT ' 할인율',
  `discount_mileage` mediumint(6) NOT NULL COMMENT '할인을 받아서 내야 하는 금액',
  `issue_date` date NOT NULL COMMENT '발행일자',
  `start_date` date NOT NULL COMMENT '쿠폰 시작일자',
  `expiration_date` date NOT NULL COMMENT '쿠폰 종료일자',
  `is_del` char(1) NOT NULL DEFAULT 'N' COMMENT '삭제여부',
  PRIMARY KEY (`idx`),
  KEY `sell_item_idx` (`sell_item_idx`),
  KEY `start_date` (`start_date`),
  KEY `expiration_date` (`expiration_date`),
  KEY `type` (`issue_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='쿠폰 리스트';

CREATE TABLE `th_coupon_member` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_coupon_member.idx PK',
  `issue_type` enum('구매','판매') NOT NULL COMMENT '발행타입',
  `coupon_idx` int(11) unsigned NOT NULL COMMENT 'th_coupon.idx FK',
  `sell_item_idx` int(11) unsigned NOT NULL COMMENT 'th_sell_item.idx FK',
  `member_idx` int(11) unsigned NOT NULL COMMENT 'th_members.idx FK',
  `subject` varchar(255) NOT NULL COMMENT '쿠폰 이름',
  `discount_rate` tinyint(3) NOT NULL COMMENT '할인율',
  `item_money` mediumint(6) NOT NULL COMMENT '상품권 가격',
  `coupon_status` int(11) NOT NULL COMMENT '쿠폰상태 th_coupon_status_code.idx FK',
  `is_coupon_del` char(1) NOT NULL DEFAULT 'N' COMMENT '쿠폰 삭제여부(th_coupon.is_del)',
  `is_del` char(1) NOT NULL DEFAULT 'N' COMMENT '사용자에게 지급된 쿠폰 삭제',
  PRIMARY KEY (`idx`),
  KEY `coupon_idx` (`coupon_idx`),
  KEY `sell_item_idx` (`sell_item_idx`),
  KEY `member_idx` (`member_idx`),
  KEY `subject` (`subject`),
  KEY `item_money` (`item_money`),
  KEY `coupon_status` (`coupon_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='유저별 쿠폰 지급  정보';

CREATE TABLE `th_coupon_status_code` (
  `idx` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_coupon_status_code.idx PK',
  `coupon_status_order` int(10) unsigned NOT NULL COMMENT '쿠폰 상태 순서',
  `coupon_status_name` varchar(30) NOT NULL COMMENT '쿠폰상태명',
  PRIMARY KEY (`idx`),
  KEY `coupon_status_name` (`coupon_status_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='쿠폰 상태 코드 테이블';

CREATE TABLE `th_coupon_useage` (
  `idx` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'th_coupon_useage.idx PK',
  `issue_type` enum('구매','판매') NOT NULL COMMENT '구분(구매, 판매)',
  `dealings_idx` int(11) unsigned NOT NULL COMMENT 'th_dealings.idx FK',
  `coupon_idx` int(11) unsigned NOT NULL COMMENT 'th_coupon.idx',
  `coupon_member_idx` int(11) unsigned NOT NULL COMMENT 'th_coupon_member.idx PK',
  `member_idx` int(11) unsigned NOT NULL COMMENT 'th_members.idx FK',
  `coupon_use_before_mileage` mediumint(6) NOT NULL COMMENT '쿠폰 사용하기전에 지불해야 하는 수수료/거래금액',
  `coupon_use_mileage` mediumint(6) NOT NULL COMMENT '쿠폰 사용해서 지불해야 하는 수수료/거래금액',
  `coupon_use_start_date` date NOT NULL COMMENT '쿠폰 사용 전 금액',
  `coupon_use_end_date` date DEFAULT NULL COMMENT '쿠폰을 완료일자(거래 완료 및 쿠폰유효기간 초과시 입력)',
  `is_refund` char(1) DEFAULT 'N' COMMENT '환불여부',
  PRIMARY KEY (`idx`),
  KEY `coupon_idx` (`coupon_idx`),
  KEY `member_idx` (`member_idx`),
  KEY `type` (`issue_type`),
  KEY `dealings_idx` (`dealings_idx`),
  KEY `coupon_use_start_date` (`coupon_use_start_date`),
  KEY `coupon_member_idx` (`coupon_member_idx`),
  KEY `coupon_use_end_date` (`coupon_use_end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='쿠폰 사용내역';
