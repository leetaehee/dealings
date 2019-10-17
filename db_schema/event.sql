/**
 * 이벤트
 */

 CREATE TABLE `imi_event_history` (
  `idx` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'imi_event_history.idx PK',
  `member_idx` int(10) unsigned NOT NULL COMMENT 'imi_members.idx FK',
  `participate_date` date NOT NULL COMMENT '이벤트 최초 참여일자',
  `participate_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '이벤트 최초 참여시각',
  `participate_count` int(11) NOT NULL COMMENT '이벤트 참여횟수',
  `event_cost` int(11) NOT NULL COMMENT '이벤트 금액',
  `event_type` enum('구매','판매') NOT NULL COMMENT '거래타입',
  PRIMARY KEY (`idx`),
  KEY `member_idx` (`member_idx`),
  KEY `dealings_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='이벤트 히스토리';
