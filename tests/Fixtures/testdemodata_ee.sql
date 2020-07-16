SET @@session.sql_mode = '';

REPLACE INTO `oxpricealarm` (`OXID`, `OXSHOPID`, `OXUSERID`, `OXEMAIL`, `OXARTID`, `OXPRICE`, `OXCURRENCY`, `OXLANG`, `OXINSERT`, `OXSENDED`, `OXTIMESTAMP`) VALUES
('_test_wished_price_8_',	2,	'123ad3b5380202966df6ff128e9eecaq',	'user@oxid-esales.com',	'_test_product_5_',	10,	'EUR',	1,	'2020-05-26 00:00:00',	'0000-00-00 00:00:00',	'2020-05-26 11:48:20');

UPDATE `oxarticles` SET `OXMAPID` = 3333, `OXVPE` = 1 WHERE OXID = '_test_product_wished_price_3_';
UPDATE `oxarticles` SET `OXMAPID` = 4444, `OXVPE` = 1 WHERE OXID = '_test_product_wished_price_4_';
UPDATE `oxarticles` SET `OXMAPID` = 1234, `OXVPE` = 1 WHERE OXID = '_test_product_for_rating_5_';
UPDATE `oxarticles` SET `OXMAPID` = 2345, `OXVPE` = 1 WHERE OXID = '_test_product_for_rating_6_';
UPDATE `oxarticles` SET `OXMAPID` = 4567, `OXVPE` = 1 WHERE OXID = '_test_product_for_rating_avg';
UPDATE `oxarticles` SET `OXMAPID` = 1123, `OXVPE` = 1 WHERE OXID='_test_product_for_wish_list';
UPDATE `oxarticles` SET `OXMAPID` = 2123, `OXVPE` = 1 WHERE OXID='_test_product_for_basket';
REPLACE INTO `oxarticles` (`OXID`, `OXMAPID`, `OXSHOPID`, `OXPARENTID`, `OXACTIVE`, `OXHIDDEN`, `OXACTIVEFROM`, `OXACTIVETO`, `OXARTNUM`, `OXEAN`, `OXDISTEAN`, `OXMPN`, `OXTITLE`, `OXSHORTDESC`, `OXPRICE`, `OXBLFIXEDPRICE`, `OXPRICEA`, `OXPRICEB`, `OXPRICEC`, `OXBPRICE`, `OXTPRICE`, `OXUNITNAME`, `OXUNITQUANTITY`, `OXEXTURL`, `OXURLDESC`, `OXURLIMG`, `OXVAT`, `OXTHUMB`, `OXICON`, `OXPIC1`, `OXPIC2`, `OXPIC3`, `OXPIC4`, `OXPIC5`, `OXPIC6`, `OXPIC7`, `OXPIC8`, `OXPIC9`, `OXPIC10`, `OXPIC11`, `OXPIC12`, `OXWEIGHT`, `OXSTOCK`, `OXSTOCKFLAG`, `OXSTOCKTEXT`, `OXNOSTOCKTEXT`, `OXDELIVERY`, `OXINSERT`, `OXTIMESTAMP`, `OXLENGTH`, `OXWIDTH`, `OXHEIGHT`, `OXFILE`, `OXSEARCHKEYS`, `OXTEMPLATE`, `OXQUESTIONEMAIL`, `OXISSEARCH`, `OXISCONFIGURABLE`, `OXVARNAME`, `OXVARSTOCK`, `OXVARCOUNT`, `OXVARSELECT`, `OXVARMINPRICE`, `OXVARMAXPRICE`, `OXVARNAME_1`, `OXVARSELECT_1`, `OXVARNAME_2`, `OXVARSELECT_2`, `OXVARNAME_3`, `OXVARSELECT_3`, `OXTITLE_1`, `OXSHORTDESC_1`, `OXURLDESC_1`, `OXSEARCHKEYS_1`, `OXTITLE_2`, `OXSHORTDESC_2`, `OXURLDESC_2`, `OXSEARCHKEYS_2`, `OXTITLE_3`, `OXSHORTDESC_3`, `OXURLDESC_3`, `OXSEARCHKEYS_3`, `OXBUNDLEID`, `OXFOLDER`, `OXSUBCLASS`, `OXSTOCKTEXT_1`, `OXSTOCKTEXT_2`, `OXSTOCKTEXT_3`, `OXNOSTOCKTEXT_1`, `OXNOSTOCKTEXT_2`, `OXNOSTOCKTEXT_3`, `OXSORT`, `OXSOLDAMOUNT`, `OXNONMATERIAL`, `OXFREESHIPPING`, `OXREMINDACTIVE`, `OXREMINDAMOUNT`, `OXAMITEMID`, `OXAMTASKID`, `OXVENDORID`, `OXMANUFACTURERID`, `OXSKIPDISCOUNTS`, `OXORDERINFO`, `OXPIXIEXPORT`, `OXPIXIEXPORTED`, `OXVPE`, `OXRATING`, `OXRATINGCNT`, `OXMINDELTIME`, `OXMAXDELTIME`, `OXDELTIMEUNIT`, `OXUPDATEPRICE`, `OXUPDATEPRICEA`, `OXUPDATEPRICEB`, `OXUPDATEPRICEC`, `OXUPDATEPRICETIME`, `OXISDOWNLOADABLE`, `OXSHOWCUSTOMAGREEMENT`) VALUES
('_test_product_wp1_',	6666,	1,	'',	1,	0,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	'123',	'',	'',	'',	'Product wp1',	'',	15,	0,	0,	0,	0,	0,	0,	'',	0,	'',	'',	'',	NULL,	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	1,	'',	'',	'0000-00-00',	'2020-05-25',	'2020-05-25 09:25:26',	0,	0,	0,	'',	'',	'',	'',	1,	0,	'',	0,	0,	'',	10,	10,	'',	'',	'',	'',	'',	'',	'Product 5',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'oxarticle',	'',	'',	'',	'',	'',	'',	0,	0,	0,	0,	0,	0,	'',	'0',	'',	'',	0,	'',	0,	'0000-00-00 00:00:00',	1,	0,	0,	0,	0,	'',	0,	0,	0,	0,	'0000-00-00 00:00:00',	0,	1),
('_test_product_wp2_',	7777,	2,	'',	1,	0,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	'213',	'',	'',	'',	'Product wp2',	'',	15,	0,	0,	0,	0,	0,	0,	'',	0,	'',	'',	'',	NULL,	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	1,	'',	'',	'0000-00-00',	'2020-05-25',	'2020-05-25 09:25:26',	0,	0,	0,	'',	'',	'',	'',	1,	0,	'',	0,	0,	'',	10,	10,	'',	'',	'',	'',	'',	'',	'Product 5',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'oxarticle',	'',	'',	'',	'',	'',	'',	0,	0,	0,	0,	0,	0,	'',	'0',	'',	'',	0,	'',	0,	'0000-00-00 00:00:00',	1,	0,	0,	0,	0,	'',	0,	0,	0,	0,	'0000-00-00 00:00:00',	0,	1),
('_test_product_5_',	5555,	2,	'',	1,	0,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	'555',	'',	'',	'',	'Product 5',	'',	15,	0,	0,	0,	0,	0,	0,	'',	0,	'',	'',	'',	NULL,	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	1,	'',	'',	'0000-00-00',	'2020-05-25',	'2020-05-25 09:25:26',	0,	0,	0,	'',	'',	'',	'',	1,	0,	'',	0,	0,	'',	10,	10,	'',	'',	'',	'',	'',	'',	'Product 5',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'oxarticle',	'',	'',	'',	'',	'',	'',	0,	0,	0,	0,	0,	0,	'',	'0',	'',	'',	0,	'',	0,	'0000-00-00 00:00:00',	1,	0,	0,	0,	0,	'',	0,	0,	0,	0,	'0000-00-00 00:00:00',	0,	1);

REPLACE INTO `oxarticles2shop` (`OXSHOPID`, `OXMAPOBJECTID`, `OXTIMESTAMP`) VALUES
(1, 3333, '2020-01-01 00:00:00'),
(1, 4444, '2020-01-01 00:00:00'),
(2, 5555, '2020-01-01 00:00:00'),
(1, 6666, '2020-01-01 00:00:00'),
(2, 7777, '2020-01-01 00:00:00'),
(1, 1234, '2020-01-01 00:00:00'),
(1, 2345, '2020-01-01 00:00:00'),
(1, 1123, '2020-01-01 00:00:00'),
(1, 2123, '2020-01-01 00:00:00'),
(1, 4567, '2020-01-01 00:00:00');

REPLACE INTO `oxuser` (`OXID`, `OXACTIVE`, `OXRIGHTS`, `OXSHOPID`, `OXUSERNAME`, `OXPASSWORD`, `OXPASSSALT`, `OXCUSTNR`, `OXUSTID`, `OXUSTIDSTATUS`, `OXCOMPANY`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXADDINFO`, `OXCITY`, `OXCOUNTRYID`, `OXSTATEID`, `OXZIP`, `OXFON`, `OXFAX`, `OXSAL`, `OXBONI`, `OXCREATE`, `OXREGISTER`, `OXPRIVFON`, `OXMOBFON`, `OXBIRTHDATE`, `OXURL`, `OXLDAPKEY`, `OXWRONGLOGINS`, `OXUPDATEKEY`, `OXUPDATEEXP`, `OXPOINTS`) VALUES
('245ad3b5380202966df6ff128e9eecaq', 1, 'user', 1, 'otheruser@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', 1, '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '0000-00-00', '', '', 0, '', 0, 0),
('123ad3b5380202966df6ff128e9eecaq', 1, 'user', 2, 'user@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', 1, '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '1984-12-22', '', '', 0, '', 0, 0),
('309db395b6c85c3881fcb9b437a73dd6', 1, 'user', 2, 'existinguser@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', 1, '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '0000-00-00', '', '', 0, '', 0, 0);

REPLACE INTO `oxratings` (`OXID`, `OXSHOPID`, `OXUSERID`, `OXTYPE`, `OXOBJECTID`, `OXRATING`) VALUES
('test_rating_8_', 2, '123ad3b5380202966df6ff128e9eecaq', 'oxarticle', '_test_product_5_', 4);
INSERT INTO oxconfig (OXID, OXSHOPID, OXVARNAME, OXVARTYPE, OXVARVALUE) SELECT
MD5(RAND()), 2, OXVARNAME, OXVARTYPE, OXVARVALUE from oxconfig;

UPDATE `oxshops` SET `OXORDEREMAIL`='reply@myoxideshop.com' WHERE `OXID`='2';

REPLACE INTO `oxaddress` (`OXID`, `OXUSERID`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXCITY`, `OXCOUNTRY`, `OXCOUNTRYID`, `OXZIP`, `OXSAL`, `OXTIMESTAMP`) VALUES
('test_delivery_address_shop_2', '123ad3b5380202966df6ff128e9eecaq', 'Marc2', 'Muster2', 'Hauptstr2', '2', 'Freiburg2', 'Germany2', 'a7c40f631fc920687.20179984', '790982', 'MR', '2020-07-14 14:12:48');
