#-------------------------------------------------------------------------------
# UK Counties - England, Wales, Scotland, Northern Ireland, Channel Islands
# v1.0 - 2003-06-14

#@counties = County.find_all
#
#for county in @counties
#    county.url_name = county.name
#    county.url_name = county.url_name.downcase
#    county.url_name = county.url_name.gsub( / /, '_' )
#    county.url_name = county.url_name.gsub( /\W*/, '' )
#    county.save
#end

#-------------------------------------------------------------------------------
drop table if exists counties;

create table counties (
	id int(10) unsigned not null auto_increment,
	code varchar(5) not null,
	name varchar(255) not null,
	url_name varchar(255) not null,
	description varchar(255) not null,
	primary key(id),
	key name(name)
);

INSERT INTO counties (code,name,description) VALUES("ALD","Alderney","TBC");
INSERT INTO counties (code,name,description) VALUES("ANT","County Antrim","TBC");
INSERT INTO counties (code,name,description) VALUES("ARM","County Armagh","TBC");
#INSERT INTO counties (code,name,description) VALUES("AVON","Avon","TBC"); #Defunct
INSERT INTO counties (code,name,description) VALUES("BSTL","Bristol","TBC"); #Added 20-10-2005 MJ
INSERT INTO counties (code,name,description) VALUES("BEDS","Bedfordshire","TBC");
INSERT INTO counties (code,name,description) VALUES("BERK","Berkshire","TBC");
INSERT INTO counties (code,name,description) VALUES("WMID","West Midlands","TBC");
INSERT INTO counties (code,name,description) VALUES("BLGW","Blaenau Gwent","TBC");
INSERT INTO counties (code,name,description) VALUES("BORD","Borders","TBC");
INSERT INTO counties (code,name,description) VALUES("BRID","Bridgend","TBC");
INSERT INTO counties (code,name,description) VALUES("BUCK","Buckinghamshire","TBC");
INSERT INTO counties (code,name,description) VALUES("CAER","Caerphilly","TBC");
INSERT INTO counties (code,name,description) VALUES("CAMB","Cambridgeshire","TBC");
INSERT INTO counties (code,name,description) VALUES("CARD","Cardiff","TBC");
INSERT INTO counties (code,name,description) VALUES("CARM","Carmarthenshire","TBC");
INSERT INTO counties (code,name,description) VALUES("CENT","Central","TBC");
INSERT INTO counties (code,name,description) VALUES("CERE","Ceredigion","TBC");
INSERT INTO counties (code,name,description) VALUES("CHES","Cheshire","TBC");
INSERT INTO counties (code,name,description) VALUES("CHI","Channel Islands","TBC");
#INSERT INTO counties (code,name,description) VALUES("CLEV","Cleveland","TBC"); #Defunct
INSERT INTO counties (code,name,description) VALUES("CLWY","Clwyd","TBC");
INSERT INTO counties (code,name,description) VALUES("CONW","Conway","TBC");
INSERT INTO counties (code,name,description) VALUES("CORN","Cornwall","TBC");
INSERT INTO counties (code,name,description) VALUES("CUMB","Cumbria","TBC");
INSERT INTO counties (code,name,description) VALUES("DENB","Denbighshire","TBC");
INSERT INTO counties (code,name,description) VALUES("DERB","Derbyshire","TBC");
INSERT INTO counties (code,name,description) VALUES("DEVO","Devon","TBC");
INSERT INTO counties (code,name,description) VALUES("DORS","Dorset","TBC");
INSERT INTO counties (code,name,description) VALUES("DOW","County Down","TBC");
INSERT INTO counties (code,name,description) VALUES("DUMF","Dumfries & Galloway","TBC");
INSERT INTO counties (code,name,description) VALUES("DURH","County Durham","TBC");
INSERT INTO counties (code,name,description) VALUES("DYFE","Dyfed","TBC");
INSERT INTO counties (code,name,description) VALUES("ESSE","Essex","TBC");
INSERT INTO counties (code,name,description) VALUES("ESUS","East Sussex","TBC");
INSERT INTO counties (code,name,description) VALUES("FER","Fermanagh","TBC");
INSERT INTO counties (code,name,description) VALUES("FIFE","Fife","TBC");
INSERT INTO counties (code,name,description) VALUES("FLIN","Flintshire","TBC");
INSERT INTO counties (code,name,description) VALUES("GLAM","Glamorgan","TBC");
INSERT INTO counties (code,name,description) VALUES("GLOU","Gloucestershire","TBC");
INSERT INTO counties (code,name,description) VALUES("GRAM","Grampian","TBC");
INSERT INTO counties (code,name,description) VALUES("GSY","Guernsey","TBC");
INSERT INTO counties (code,name,description) VALUES("GWEN","Gwent","TBC");
INSERT INTO counties (code,name,description) VALUES("GWYN","Gwynedd","TBC");
INSERT INTO counties (code,name,description) VALUES("GWYN","Gwynedd","TBC");
INSERT INTO counties (code,name,description) VALUES("HAMP","Hampshire","TBC");
INSERT INTO counties (code,name,description) VALUES("HERE","Herefordshire","TBC"); # Changed from Hereford & Worcester - MJ
INSERT INTO counties (code,name,description) VALUES("HERT","Hertfordshire","TBC");
INSERT INTO counties (code,name,description) VALUES("EYOR","East Riding of Yorkshire","TBC"); # Changed from Huberside - MJ
INSERT INTO counties (code,name,description) VALUES("ISLE","Isle of Anglesey","TBC");
INSERT INTO counties (code,name,description) VALUES("JSY","Jersey","TBC");
INSERT INTO counties (code,name,description) VALUES("JSY","Jersey","TBC");
INSERT INTO counties (code,name,description) VALUES("KENT","Kent","TBC");
INSERT INTO counties (code,name,description) VALUES("LANC","Lancashire","TBC");
INSERT INTO counties (code,name,description) VALUES("LDY","Londonderry","TBC");
INSERT INTO counties (code,name,description) VALUES("LEIC","Leicestershire","TBC");
INSERT INTO counties (code,name,description) VALUES("LINC","Lincolnshire","TBC");
INSERT INTO counties (code,name,description) VALUES("LOND","London","TBC");
INSERT INTO counties (code,name,description) VALUES("LOTH","Lothian","TBC");
INSERT INTO counties (code,name,description) VALUES("MANC","Manchester","TBC");
INSERT INTO counties (code,name,description) VALUES("MERS","Merseyside","TBC");
INSERT INTO counties (code,name,description) VALUES("MDSX","Middlesex","TBC");
INSERT INTO counties (code,name,description) VALUES("MERT","Merthyr Tydfil","TBC");
INSERT INTO counties (code,name,description) VALUES("MONM","Monmouthshire","TBC");
INSERT INTO counties (code,name,description) VALUES("NEAT","Neath Port Talbot","TBC");
INSERT INTO counties (code,name,description) VALUES("NEWP","Newport","TBC");
INSERT INTO counties (code,name,description) VALUES("NHAM","Northamptonshire","TBC");
INSERT INTO counties (code,name,description) VALUES("NORF","Norfolk","TBC");
INSERT INTO counties (code,name,description) VALUES("NOTT","Nottinghamshire","TBC");
INSERT INTO counties (code,name,description) VALUES("NUMB","Northumberland","TBC");
INSERT INTO counties (code,name,description) VALUES("NWHI","North west Highlands","TBC");
INSERT INTO counties (code,name,description) VALUES("NYOR","North Yorkshire","TBC");
INSERT INTO counties (code,name,description) VALUES("OXFO","Oxfordshire","TBC");
INSERT INTO counties (code,name,description) VALUES("PEMB","Pembrokeshire","TBC");
INSERT INTO counties (code,name,description) VALUES("POWY","Powys","TBC");
INSERT INTO counties (code,name,description) VALUES("POWY","Powys","TBC");
INSERT INTO counties (code,name,description) VALUES("SHRO","Shropshire","TBC");
INSERT INTO counties (code,name,description) VALUES("SOME","Somerset","TBC");
INSERT INTO counties (code,name,description) VALUES("STAF","Staffordshire","TBC");
INSERT INTO counties (code,name,description) VALUES("STRA","Strathclyde","TBC");
INSERT INTO counties (code,name,description) VALUES("SUFF","Suffolk","TBC");
INSERT INTO counties (code,name,description) VALUES("SURR","Surrey","TBC");
INSERT INTO counties (code,name,description) VALUES("SWAN","Swansea","TBC");
INSERT INTO counties (code,name,description) VALUES("SYOR","South Yorkshire","TBC");
INSERT INTO counties (code,name,description) VALUES("TAFF","Rhondda Cynon Taff","TBC");
INSERT INTO counties (code,name,description) VALUES("TAYS","Tayside","TBC");
INSERT INTO counties (code,name,description) VALUES("TORF","Torfaen","TBC");
INSERT INTO counties (code,name,description) VALUES("TYR","County Tyrone","TBC");
INSERT INTO counties (code,name,description) VALUES("TYWE","Tyne & Wear","TBC");
INSERT INTO counties (code,name,description) VALUES("VALE","Vale of Glamorgan","TBC");
INSERT INTO counties (code,name,description) VALUES("WORC","Worcestershire","TBC");
INSERT INTO counties (code,name,description) VALUES("WARW","Warwickshire","TBC");
INSERT INTO counties (code,name,description) VALUES("WILT","Wiltshire","TBC");
INSERT INTO counties (code,name,description) VALUES("WISL","West Isles","TBC");
INSERT INTO counties (code,name,description) VALUES("WREX","Wrexham","TBC");
INSERT INTO counties (code,name,description) VALUES("WSUS","West Sussex","TBC");
INSERT INTO counties (code,name,description) VALUES("WYOR","West Yorkshire","TBC");