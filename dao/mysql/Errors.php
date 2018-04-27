<?php
namespace ITRocks\Framework\Dao\Mysql;

// phpcs:ignoreFile -- code ordered constants
/**
 * Official mysql error codes, stored into constants
 *
 * TODO LOW complete this
 *
 * @link https://dev.mysql.com/doc/refman/5.7/en/error-messages-server.html
 */
class Errors
{

	//------------------------------------------------------------------------------------ ER_HASHCHK
	const ER_HASHCHK = 1000;
	const ER_NISAMCHK = 1001;
	const ER_NO = 1002;
	const ER_YES = 1003;
	const ER_CANT_CREATE_FILE = 1004;
	const ER_CANT_CREATE_TABLE = 1005;
	const ER_CANT_CREATE_DB = 1006;
	const ER_DB_CREATE_EXISTS = 1007;
	const ER_DB_DROP_EXISTS = 1008;
	const ER_DB_DROP_DELETE = 1009;
	const ER_DB_DROP_RMDIR = 1010;
	const ER_CANT_DELETE_FILE = 1011;
	const ER_CANT_FIND_SYSTEM_REC = 1012;
	const ER_CANT_GET_STAT = 1013;
	const ER_CANT_GET_WD = 1014;
	const ER_CANT_LOCK = 1015;
	const ER_CANT_OPEN_FILE = 1016;
	const ER_FILE_NOT_FOUND = 1017;
	const ER_CANT_READ_DIR = 1018;
	const ER_CANT_SET_WD = 1019;
	const ER_CHECKREAD = 1020;
	const ER_DISK_FULL = 1021;
	const ER_DUP_KEY = 1022;
	const ER_ERROR_ON_CLOSE = 1023;
	const ER_ERROR_ON_READ = 1024;
	const ER_ERROR_ON_RENAME = 1025;
	const ER_ERROR_ON_WRITE = 1026;
	const ER_FILE_USED = 1027;
	const ER_FILSORT_ABORT = 1028;
	const ER_FORM_NOT_FOUND = 1029;
	const ER_GET_ERRNO = 1030;
	const ER_ILLEGAL_HA = 1031;
	const ER_KEY_NOT_FOUND = 1032;
	const ER_NOT_FORM_FILE = 1033;
	const ER_NOT_KEYFILE = 1034;
	const ER_OLD_KEYFILE = 1035;
	const ER_OPEN_AS_READONLY = 1036;
	const ER_OUTOFMEMORY = 1037;
	const ER_OUT_OF_SORTMEMORY = 1038;
	const ER_UNEXPECTED_EOF = 1039;
	const ER_CON_COUNT_ERROR = 1040;
	const ER_OUT_OF_RESOURCES = 1041;
	const ER_BAD_HOST_ERROR = 1042;
	const ER_HANDSHAKE_ERROR = 1043;
	const ER_DBACCESS_DENIED_ERROR = 1044;
	const ER_ACCESS_DENIED_ERROR = 1045;
	const ER_NO_DB_ERROR = 1046;
	const ER_UNKNOWN_COM_ERROR = 1047;
	const ER_BAD_NULL_ERROR = 1048;
	const ER_BAD_DB_ERROR = 1049;
	const ER_TABLE_EXISTS_ERROR = 1050;
	const ER_BAD_TABLE_ERROR = 1051;
	const ER_NON_UNIQ_ERROR = 1052;
	const ER_SERVER_SHUTDOWN = 1053;
	const ER_BAD_FIELD_ERROR = 1054;
	const ER_WRONG_FIELD_WITH_GROUP = 1055;
	const ER_WRONG_GROUP_FIELD = 1056;
	const ER_WRONG_SUM_SELECT = 1057;
	const ER_WRONG_VALUE_COUNT = 1058;
	const ER_TOO_LONG_IDENT = 1059;
	const ER_DUP_FIELDNAME = 1060;
	const ER_DUP_KEYNAME = 1061;
	const ER_DUP_ENTRY = 1062;
	const ER_WRONG_FIELD_SPEC = 1063;
	const ER_PARSE_ERROR = 1064;
	const ER_EMPTY_QUERY = 1065;
	const ER_NONUNIQ_TABLE = 1066;
	const ER_INVALID_DEFAULT = 1067;
	const ER_MULTIPLE_PRI_KEY = 1068;
	const ER_TOO_MANY_KEYS = 1069;
	const ER_TOO_MANY_KEY_PARTS = 1070;
	const ER_TOO_LONG_KEY = 1071;
	const ER_KEY_COLUMN_DOES_NOT_EXITS = 1072;
	const ER_BLOB_USED_AS_KEY = 1073;
	const ER_TOO_BIG_FIELDLENGTH = 1074;
	const ER_WRONG_AUTO_KEY = 1075;
	const ER_READY = 1076;
	const ER_NORMAL_SHUTDOWN = 1077;
	const ER_GOT_SIGNAL = 1078;
	const ER_SHUTDOWN_COMPLETE = 1079;
	const ER_FORCING_CLOSE = 1080;
	const ER_IPSOCK_ERROR = 1081;
	const ER_NO_SUCH_INDEX = 1082;
	const ER_WRONG_FIELD_TERMINATORS = 1083;
	const ER_BLOBS_AND_NO_TERMINATED = 1084;
	const ER_TEXTFILE_NOT_READABLE = 1085;
	const ER_FILE_EXISTS_ERROR = 1086;
	const ER_LOAD_INFO = 1087;
	const ER_ALTER_INFO = 1088;
	const ER_WRONG_SUB_KEY = 1089;
	const ER_CANT_REMOVE_ALL_FIELDS = 1090;
	const ER_CANT_DROP_FIELD_OR_KEY = 1091;
	const ER_INSERT_INFO = 1092;
	const ER_UPDATE_TABLE_USED = 1093;
	const ER_NO_SUCH_THREAD = 1094;
	const ER_KILL_DENIED_ERROR = 1095;
	const ER_NO_TABLES_USED = 1096;
	const ER_TOO_BIG_SET = 1097;
	const ER_NO_UNIQUE_LOGFILE = 1098;
	const ER_TABLE_NOT_LOCKED_FOR_WRITE = 1099;
	const ER_TABLE_NOT_LOCKED = 1100;
	const ER_BLOB_CANT_HAVE_DEFAULT = 1101;
	const ER_WRONG_DB_NAME = 1102;
	const ER_WRONG_TABLE_NAME = 1103;
	const ER_TOO_BIG_SELECT = 1104;
	const ER_UNKNOWN_ERROR = 1105;
	const ER_UNKNOWN_PROCEDURE = 1106;
	const ER_WRONG_PARAMCOUNT_TO_PROCEDURE = 1107;
	const ER_WRONG_PARAMETERS_TO_PROCEDURE = 1108;
	const ER_UNKNOWN_TABLE = 1109;
	const ER_FIELD_SPECIFIED_TWICE = 1110;
	const ER_INVALID_GROUP_FUNC_USE = 1111;
	const ER_UNSUPPORTED_EXTENSION = 1112;
	const ER_TABLE_MUST_HAVE_COLUMNS = 1113;
	const ER_RECORD_FILE_FULL = 1114;
	const ER_UNKNOWN_CHARACTER_SET = 1115;
	const ER_TOO_MANY_TABLES = 1116;
	const ER_TOO_MANY_FIELDS = 1117;
	const ER_TOO_BIG_ROWSIZE = 1118;
	const ER_STACK_OVERRUN = 1119;
	const ER_WRONG_OUTER_JOIN = 1120;
	const ER_NULL_COLUMN_IN_INDEX = 1121;
	const ER_CANT_FIND_UDF = 1122;
	const ER_CANT_INITIALIZE_UDF = 1123;
	const ER_UDF_NO_PATHS = 1124;
	const ER_UDF_EXISTS = 1125;
	const ER_CANT_OPEN_LIBRARY = 1126;
	const ER_CANT_FIND_DL_ENTRY = 1127;
	const ER_FUNCTION_NOT_DEFINED = 1128;
	const ER_HOST_IS_BLOCKED = 1129;
	const ER_HOST_NOT_PRIVILEGED = 1130;
	const ER_PASSWORD_ANONYMOUS_USER = 1131;
	const ER_PASSWORD_NOT_ALLOWED = 1132;
	const ER_PASSWORD_NO_MATCH = 1133;
	const ER_UPDATE_INFO = 1134;
	const ER_CANT_CREATE_THREAD = 1135;
	const ER_WRONG_VALUE_COUNT_ON_ROW = 1136;
	const ER_CANT_REOPEN_TABLE = 1137;
	const ER_INVALID_USE_OF_NULL = 1138;
	const ER_REGEXP_ERROR = 1139;
	const ER_MIX_OF_GROUP_FUNC_AND_FIELDS = 1140;
	const ER_NONEXISTING_GRANT = 1141;
	const ER_TABLEACCESS_DENIED_ERROR = 1142;
	const ER_COLUMNACCESS_DENIED_ERROR = 1143;
	const ER_ILLEGAL_GRANT_FOR_TABLE = 1144;
	const ER_GRANT_WRONG_HOST_OR_USER = 1145;
	const ER_NO_SUCH_TABLE = 1146;
	const ER_NONEXISTING_TABLE_GRANT = 1147;
	const ER_NOT_ALLOWED_COMMAND = 1148;
	const ER_SYNTAX_ERROR = 1149;
	const ER_DELAYED_CANT_CHANGE_LOCK = 1150;
	const ER_TOO_MANY_DELAYED_THREADS = 1151;
	const ER_ABORTING_CONNECTION = 1152;
	const ER_NET_PACKET_TOO_LARGE = 1153;
	const ER_NET_READ_ERROR_FROM_PIPE = 1154;
	const ER_NET_FCNTL_ERROR = 1155;
	const ER_NET_PACKETS_OUT_OF_ORDER = 1156;
	const ER_NET_UNCOMPRESS_ERROR = 1157;
	const ER_NET_READ_ERROR = 1158;
	const ER_NET_READ_INTERRUPTED = 1159;
	const ER_NET_ERROR_ON_WRITE = 1160;
	const ER_NET_WRITE_INTERRUPTED = 1161;
	const ER_TOO_LONG_STRING = 1162;
	const ER_TABLE_CANT_HANDLE_BLOB = 1163;
	const ER_TABLE_CANT_HANDLE_AUTO_INCREMENT = 1164;
	const ER_DELAYED_INSERT_TABLE_LOCKED = 1165;
	const ER_WRONG_COLUMN_NAME = 1166;
	const ER_WRONG_KEY_COLUMN = 1167;
	const ER_WRONG_MRG_TABLE = 1168;
	const ER_DUP_UNIQUE = 1169;
	const ER_BLOB_KEY_WITHOUT_LENGTH = 1170;
	const ER_PRIMARY_CANT_HAVE_NULL = 1171;
	const ER_TOO_MANY_ROWS = 1172;
	const ER_REQUIRES_PRIMARY_KEY = 1173;
	const ER_NO_RAID_COMPILED = 1174;
	const ER_UPDATE_WITHOUT_KEY_IN_SAFE_MODE = 1175;
	const ER_KEY_DOES_NOT_EXITS = 1176;
	const ER_CHECK_NO_SUCH_TABLE = 1177;
	const ER_CHECK_NOT_IMPLEMENTED = 1178;
	const ER_CANT_DO_THIS_DURING_AN_TRANSACTION = 1179;
	const ER_ERROR_DURING_COMMIT = 1180;
	const ER_ERROR_DURING_ROLLBACK = 1181;
	const ER_ERROR_DURING_FLUSH_LOGS = 1182;
	const ER_ERROR_DURING_CHECKPOINT = 1183;
	const ER_NEW_ABORTING_CONNECTION = 1184;
	const ER_DUMP_NOT_IMPLEMENTED = 1185;
	const ER_FLUSH_MASTER_BINLOG_CLOSED = 1186;
	const ER_INDEX_REBUILD = 1187;
	const ER_MASTER = 1188;
	const ER_MASTER_NET_READ = 1189;
	const ER_MASTER_NET_WRITE = 1190;
	const ER_FT_MATCHING_KEY_NOT_FOUND = 1191;
	const ER_LOCK_OR_ACTIVE_TRANSACTION = 1192;
	const ER_UNKNOWN_SYSTEM_VARIABLE = 1193;
	const ER_CRASHED_ON_USAGE = 1194;
	const ER_CRASHED_ON_REPAIR = 1195;
	const ER_WARNING_NOT_COMPLETE_ROLLBACK = 1196;
	const ER_TRANS_CACHE_FULL = 1197;
	const ER_SLAVE_MUST_STOP = 1198;
	const ER_SLAVE_NOT_RUNNING = 1199;
	const ER_BAD_SLAVE = 1200;
	const ER_MASTER_INFO = 1201;
	const ER_SLAVE_THREAD = 1202;
	const ER_TOO_MANY_USER_CONNECTIONS = 1203;
	const ER_SET_CONSTANTS_ONLY = 1204;
	const ER_LOCK_WAIT_TIMEOUT = 1205;
	const ER_LOCK_TABLE_FULL = 1206;
	const ER_READ_ONLY_TRANSACTION = 1207;
	const ER_DROP_DB_WITH_READ_LOCK = 1208;
	const ER_CREATE_DB_WITH_READ_LOCK = 1209;
	const ER_WRONG_ARGUMENTS = 1210;
	const ER_NO_PERMISSION_TO_CREATE_USER = 1211;
	const ER_UNION_TABLES_IN_DIFFERENT_DIR = 1212;
	const ER_LOCK_DEADLOCK = 1213;
	const ER_TABLE_CANT_HANDLE_FT = 1214;
	const ER_CANNOT_ADD_FOREIGN = 1215;
	const ER_NO_REFERENCED_ROW = 1216;
	const ER_ROW_IS_REFERENCED = 1217;
	const ER_CONNECT_TO_MASTER = 1218;
	const ER_QUERY_ON_MASTER = 1219;
	const ER_ERROR_WHEN_EXECUTING_COMMAND = 1220;
	const ER_WRONG_USAGE = 1221;
	const ER_WRONG_NUMBER_OF_COLUMNS_IN_SELECT = 1222;
	const ER_CANT_UPDATE_WITH_READLOCK = 1223;
	const ER_MIXING_NOT_ALLOWED = 1224;
	const ER_DUP_ARGUMENT = 1225;
	const ER_USER_LIMIT_REACHED = 1226;
	const ER_SPECIFIC_ACCESS_DENIED_ERROR = 1227;
	const ER_LOCAL_VARIABLE = 1228;
	const ER_GLOBAL_VARIABLE = 1229;
	const ER_NO_DEFAULT = 1230;
	const ER_WRONG_VALUE_FOR_VAR = 1231;
	const ER_WRONG_TYPE_FOR_VAR = 1232;
	const ER_VAR_CANT_BE_READ = 1233;
	const ER_CANT_USE_OPTION_HERE = 1234;
	const ER_NOT_SUPPORTED_YET = 1235;
	const ER_MASTER_FATAL_ERROR_READING_BINLOG = 1236;
	const ER_SLAVE_IGNORED_TABLE = 1237;
	const ER_INCORRECT_GLOBAL_LOCAL_VAR = 1238;
	const ER_WRONG_FK_DEF = 1239;
	const ER_KEY_REF_DO_NOT_MATCH_TABLE_REF = 1240;
	const ER_OPERAND_COLUMNS = 1241;
	const ER_SUBQUERY_NO_1_ROW = 1242;
	const ER_UNKNOWN_STMT_HANDLER = 1243;
	const ER_CORRUPT_HELP_DB = 1244;
	const ER_CYCLIC_REFERENCE = 1245;
	const ER_AUTO_CONVERT = 1246;
	const ER_ILLEGAL_REFERENCE = 1247;
	const ER_DERIVED_MUST_HAVE_ALIAS = 1248;
	const ER_SELECT_REDUCED = 1249;
	const ER_TABLENAME_NOT_ALLOWED_HERE = 1250;
	const ER_NOT_SUPPORTED_AUTH_MODE = 1251;
	const ER_SPATIAL_CANT_HAVE_NULL = 1252;
	const ER_COLLATION_CHARSET_MISMATCH = 1253;
	const ER_SLAVE_WAS_RUNNING = 1254;
	const ER_SLAVE_WAS_NOT_RUNNING = 1255;
	const ER_TOO_BIG_FOR_UNCOMPRESS = 1256;
	const ER_ZLIB_Z_MEM_ERROR = 1257;
	const ER_ZLIB_Z_BUF_ERROR = 1258;
	const ER_ZLIB_Z_DATA_ERROR = 1259;
	const ER_CUT_VALUE_GROUP_CONCAT = 1260;
	const ER_WARN_TOO_FEW_RECORDS = 1261;
	const ER_WARN_TOO_MANY_RECORDS = 1262;
	const ER_WARN_NULL_TO_NOTNULL = 1263;
	const ER_WARN_DATA_OUT_OF_RANGE = 1264;
	const WARN_DATA_TRUNCATED = 1265;
	const ER_WARN_USING_OTHER_HANDLER = 1266;
	const ER_CANT_AGGREGATE_2COLLATIONS = 1267;
	const ER_DROP_USER = 1268;
	const ER_REVOKE_GRANTS = 1269;
	const ER_CANT_AGGREGATE_3COLLATIONS = 1270;
	const ER_CANT_AGGREGATE_NCOLLATIONS = 1271;
	const ER_VARIABLE_IS_NOT_STRUCT = 1272;
	const ER_UNKNOWN_COLLATION = 1273;
	const ER_SLAVE_IGNORED_SSL_PARAMS = 1274;
	const ER_SERVER_IS_IN_SECURE_AUTH_MODE = 1275;
	const ER_WARN_FIELD_RESOLVED = 1276;
	const ER_BAD_SLAVE_UNTIL_COND = 1277;
	const ER_MISSING_SKIP_SLAVE = 1278;
	const ER_UNTIL_COND_IGNORED = 1279;
	const ER_WRONG_NAME_FOR_INDEX = 1280;
	const ER_WRONG_NAME_FOR_CATALOG = 1281;
	const ER_WARN_QC_RESIZE = 1282;
	const ER_BAD_FT_COLUMN = 1283;
	const ER_UNKNOWN_KEY_CACHE = 1284;
	const ER_WARN_HOSTNAME_WONT_WORK = 1285;
	const ER_UNKNOWN_STORAGE_ENGINE = 1286;
	const ER_WARN_DEPRECATED_SYNTAX = 1287;
	const ER_NON_UPDATABLE_TABLE = 1288;
	const ER_FEATURE_DISABLED = 1289;
	const ER_OPTION_PREVENTS_STATEMENT = 1290;
	const ER_DUPLICATED_VALUE_IN_TYPE = 1291;
	const ER_TRUNCATED_WRONG_VALUE = 1292;
	const ER_TOO_MUCH_AUTO_TIMESTAMP_COLS = 1293;
	const ER_INVALID_ON_UPDATE = 1294;
	const ER_UNSUPPORTED_PS = 1295;
	const ER_GET_ERRMSG = 1296;
	const ER_GET_TEMPORARY_ERRMSG = 1297;
	const ER_UNKNOWN_TIME_ZONE = 1298;
	const ER_WARN_INVALID_TIMESTAMP = 1299;
	const ER_INVALID_CHARACTER_STRING = 1300;
	const ER_WARN_ALLOWED_PACKET_OVERFLOWED = 1301;
	const ER_CONFLICTING_DECLARATIONS = 1302;
	const ER_SP_NO_RECURSIVE_CREATE = 1303;
	const ER_SP_ALREADY_EXISTS = 1304;
	const ER_SP_DOES_NOT_EXIST = 1305;
	const ER_SP_DROP_FAILED = 1306;
	const ER_SP_STORE_FAILED = 1307;
	const ER_SP_LILABEL_MISMATCH = 1308;
	const ER_SP_LABEL_REDEFINE = 1309;
	const ER_SP_LABEL_MISMATCH = 1310;
	const ER_SP_UNINIT_VAR = 1311;
	const ER_SP_BADSELECT = 1312;
	const ER_SP_BADRETURN = 1313;
	const ER_SP_BADSTATEMENT = 1314;
	const ER_UPDATE_LOG_DEPRECATED_IGNORED = 1315;
	const ER_UPDATE_LOG_DEPRECATED_TRANSLATED = 1316;
	const ER_QUERY_INTERRUPTED = 1317;
	const ER_SP_WRONG_NO_OF_ARGS = 1318;
	const ER_SP_COND_MISMATCH = 1319;
	const ER_SP_NORETURN = 1320;
	const ER_SP_NORETURNEND = 1321;
	const ER_SP_BAD_CURSOR_QUERY = 1322;
	const ER_SP_BAD_CURSOR_SELECT = 1323;
	const ER_SP_CURSOR_MISMATCH = 1324;
	const ER_SP_CURSOR_ALREADY_OPEN = 1325;
	const ER_SP_CURSOR_NOT_OPEN = 1326;
	const ER_SP_UNDECLARED_VAR = 1327;
	const ER_SP_WRONG_NO_OF_FETCH_ARGS = 1328;
	const ER_SP_FETCH_NO_DATA = 1329;
	const ER_SP_DUP_PARAM = 1330;
	const ER_SP_DUP_VAR = 1331;
	const ER_SP_DUP_COND = 1332;
	const ER_SP_DUP_CURS = 1333;
	const ER_SP_CANT_ALTER = 1334;
	const ER_SP_SUBSELECT_NYI = 1335;
	const ER_STMT_NOT_ALLOWED_IN_SF_OR_TRG = 1336;
	const ER_SP_VARCOND_AFTER_CURSHNDLR = 1337;
	const ER_SP_CURSOR_AFTER_HANDLER = 1338;
	const ER_SP_CASE_NOT_FOUND = 1339;
	const ER_FPARSER_TOO_BIG_FILE = 1340;
	const ER_FPARSER_BAD_HEADER = 1341;
	const ER_FPARSER_EOF_IN_COMMENT = 1342;
	const ER_FPARSER_ERROR_IN_PARAMETER = 1343;
	const ER_FPARSER_EOF_IN_UNKNOWN_PARAMETER = 1344;
	const ER_VIEW_NO_EXPLAIN = 1345;
	const ER_FRM_UNKNOWN_TYPE = 1346;
	const ER_WRONG_OBJECT = 1347;
	const ER_NONUPDATEABLE_COLUMN = 1348;
	const ER_VIEW_SELECT_DERIVED = 1349;
	const ER_VIEW_SELECT_CLAUSE = 1350;
	const ER_VIEW_SELECT_VARIABLE = 1351;
	const ER_VIEW_SELECT_TMPTABLE = 1352;
	const ER_VIEW_WRONG_LIST = 1353;
	const ER_WARN_VIEW_MERGE = 1354;
	const ER_WARN_VIEW_WITHOUT_KEY = 1355;
	const ER_VIEW_INVALID = 1356;
	const ER_SP_NO_DROP_SP = 1357;
	const ER_SP_GOTO_IN_HNDLR = 1358;
	const ER_TRG_ALREADY_EXISTS = 1359;
	const ER_TRG_DOES_NOT_EXIST = 1360;
	const ER_TRG_ON_VIEW_OR_TEMP_TABLE = 1361;
	const ER_TRG_CANT_CHANGE_ROW = 1362;
	const ER_TRG_NO_SUCH_ROW_IN_TRG = 1363;
	const ER_NO_DEFAULT_FOR_FIELD = 1364;
	const ER_DIVISION_BY_ZERO = 1365;
	const ER_TRUNCATED_WRONG_VALUE_FOR_FIELD = 1366;
	const ER_ILLEGAL_VALUE_FOR_TYPE = 1367;
	const ER_VIEW_NONUPD_CHECK = 1368;
	const ER_VIEW_CHECK_FAILED = 1369;
	const ER_PROCACCESS_DENIED_ERROR = 1370;
	const ER_RELAY_LOG_FAIL = 1371;
	const ER_PASSWD_LENGTH = 1372;
	const ER_UNKNOWN_TARGET_BINLOG = 1373;
	const ER_IO_ERR_LOG_INDEX_READ = 1374;
	const ER_BINLOG_PURGE_PROHIBITED = 1375;
	const ER_FSEEK_FAIL = 1376;
	const ER_BINLOG_PURGE_FATAL_ERR = 1377;
	const ER_LOG_IN_USE = 1378;
	const ER_LOG_PURGE_UNKNOWN_ERR = 1379;
	const ER_RELAY_LOG_INIT = 1380;
	const ER_NO_BINARY_LOGGING = 1381;
	const ER_RESERVED_SYNTAX = 1382;
	const ER_WSAS_FAILED = 1383;
	const ER_DIFF_GROUPS_PROC = 1384;
	const ER_NO_GROUP_FOR_PROC = 1385;
	const ER_ORDER_WITH_PROC = 1386;
	const ER_LOGGING_PROHIBIT_CHANGING_OF = 1387;
	const ER_NO_FILE_MAPPING = 1388;
	const ER_WRONG_MAGIC = 1389;
	const ER_PS_MANY_PARAM = 1390;
	const ER_KEY_PART_0 = 1391;
	const ER_VIEW_CHECKSUM = 1392;
	const ER_VIEW_MULTIUPDATE = 1393;
	const ER_VIEW_NO_INSERT_FIELD_LIST = 1394;
	const ER_VIEW_DELETE_MERGE_VIEW = 1395;
	const ER_CANNOT_USER = 1396;
	const ER_XAER_NOTA = 1397;
	const ER_XAER_INVAL = 1398;
	const ER_XAER_RMFAIL = 1399;
	const ER_XAER_OUTSIDE = 1400;
	const ER_XAER_RMERR = 1401;
	const ER_XA_RBROLLBACK = 1402;
	const ER_NONEXISTING_PROC_GRANT = 1403;
	const ER_PROC_AUTO_GRANT_FAIL = 1404;
	const ER_PROC_AUTO_REVOKE_FAIL = 1405;
	const ER_DATA_TOO_LONG = 1406;
	const ER_SP_BAD_SQLSTATE = 1407;
	const ER_STARTUP = 1408;
	const ER_LOAD_FROM_FIXED_SIZE_ROWS_TO_VAR = 1409;
	const ER_CANT_CREATE_USER_WITH_GRANT = 1410;
	const ER_WRONG_VALUE_FOR_TYPE = 1411;
	const ER_TABLE_DEF_CHANGED = 1412;
	const ER_SP_DUP_HANDLER = 1413;
	const ER_SP_NOT_VAR_ARG = 1414;
	const ER_SP_NO_RETSET = 1415;
	const ER_CANT_CREATE_GEOMETRY_OBJECT = 1416;
	const ER_FAILED_ROUTINE_BREAK_BINLOG = 1417;
	const ER_BINLOG_UNSAFE_ROUTINE = 1418;
	const ER_BINLOG_CREATE_ROUTINE_NEED_SUPER = 1419;
	const ER_EXEC_STMT_WITH_OPEN_CURSOR = 1420;
	const ER_STMT_HAS_NO_OPEN_CURSOR = 1421;
	const ER_COMMIT_NOT_ALLOWED_IN_SF_OR_TRG = 1422;
	const ER_NO_DEFAULT_FOR_VIEW_FIELD = 1423;
	const ER_SP_NO_RECURSION = 1424;
	const ER_TOO_BIG_SCALE = 1425;
	const ER_TOO_BIG_PRECISION = 1426;
	const ER_M_BIGGER_THAN_D = 1427;
	const ER_WRONG_LOCK_OF_SYSTEM_TABLE = 1428;
	const ER_CONNECT_TO_FOREIGN_DATA_SOURCE = 1429;
	const ER_QUERY_ON_FOREIGN_DATA_SOURCE = 1430;
	const ER_FOREIGN_DATA_SOURCE_DOESNT_EXIST = 1431;
	const ER_FOREIGN_DATA_STRING_INVALID_CANT_CREATE = 1432;
	const ER_FOREIGN_DATA_STRING_INVALID = 1433;
	const ER_CANT_CREATE_FEDERATED_TABLE = 1434;
	const ER_TRG_IN_WRONG_SCHEMA = 1435;
	const ER_STACK_OVERRUN_NEED_MORE = 1436;
	const ER_TOO_LONG_BODY = 1437;
	const ER_WARN_CANT_DROP_DEFAULT_KEYCACHE = 1438;
	const ER_TOO_BIG_DISPLAYWIDTH = 1439;
	const ER_XAER_DUPID = 1440;
	const ER_DATETIME_FUNCTION_OVERFLOW = 1441;
	const ER_CANT_UPDATE_USED_TABLE_IN_SF_OR_TRG = 1442;
	const ER_VIEW_PREVENT_UPDATE = 1443;
	const ER_PS_NO_RECURSION = 1444;
	const ER_SP_CANT_SET_AUTOCOMMIT = 1445;
	const ER_MALFORMED_DEFINER = 1446;
	const ER_VIEW_FRM_NO_USER = 1447;
	const ER_VIEW_OTHER_USER = 1448;
	const ER_NO_SUCH_USER = 1449;
	const ER_FORBID_SCHEMA_CHANGE = 1450;
	const ER_ROW_IS_REFERENCED_2 = 1451;
	const ER_NO_REFERENCED_ROW_2 = 1452;
	const ER_SP_BAD_VAR_SHADOW = 1453;
	const ER_TRG_NO_DEFINER = 1454;
	const ER_OLD_FILE_FORMAT = 1455;
	const ER_SP_RECURSION_LIMIT = 1456;
	const ER_SP_PROC_TABLE_CORRUPT = 1457;
	const ER_SP_WRONG_NAME = 1458;
	const ER_TABLE_NEEDS_UPGRADE = 1459;
	const ER_SP_NO_AGGREGATE = 1460;
	const ER_MAX_PREPARED_STMT_COUNT_REACHED = 1461;
	const ER_VIEW_RECURSIVE = 1462;
	const ER_NON_GROUPING_FIELD_USED = 1463;
	const ER_TABLE_CANT_HANDLE_SPKEYS = 1464;
	const ER_NO_TRIGGERS_ON_SYSTEM_SCHEMA = 1465;
	const ER_REMOVED_SPACES = 1466;
	const ER_AUTOINC_READ_FAILED = 1467;
	const ER_USERNAME = 1468;
	const ER_HOSTNAME = 1469;
	const ER_WRONG_STRING_LENGTH = 1470;
	const ER_NON_INSERTABLE_TABLE = 1471;
	const ER_ADMIN_WRONG_MRG_TABLE = 1472;
	const ER_TOO_HIGH_LEVEL_OF_NESTING_FOR_SELECT = 1473;
	const ER_NAME_BECOMES_EMPTY = 1474;
	const ER_AMBIGUOUS_FIELD_TERM = 1475;
	const ER_FOREIGN_SERVER_EXISTS = 1476;
	const ER_FOREIGN_SERVER_DOESNT_EXIST = 1477;
	const ER_ILLEGAL_HA_CREATE_OPTION = 1478;
	const ER_PARTITION_REQUIRES_VALUES_ERROR = 1479;
	const ER_PARTITION_WRONG_VALUES_ERROR = 1480;
	const ER_PARTITION_MAXVALUE_ERROR = 1481;
	const ER_PARTITION_SUBPARTITION_ERROR = 1482;
	const ER_PARTITION_SUBPART_MIX_ERROR = 1483;
	const ER_PARTITION_WRONG_NO_PART_ERROR = 1484;
	const ER_PARTITION_WRONG_NO_SUBPART_ERROR = 1485;
	const ER_WRONG_EXPR_IN_PARTITION_FUNC_ERROR = 1486;
	const ER_NO_CONST_EXPR_IN_RANGE_OR_LIST_ERROR = 1487;
	const ER_FIELD_NOT_FOUND_PART_ERROR = 1488;
	const ER_LIST_OF_FIELDS_ONLY_IN_HASH_ERROR = 1489;
	const ER_INCONSISTENT_PARTITION_INFO_ERROR = 1490;
	const ER_PARTITION_FUNC_NOT_ALLOWED_ERROR = 1491;
	const ER_PARTITIONS_MUST_BE_DEFINED_ERROR = 1492;
	const ER_RANGE_NOT_INCREASING_ERROR = 1493;
	const ER_INCONSISTENT_TYPE_OF_FUNCTIONS_ERROR = 1494;
	const ER_MULTIPLE_DEF_CONST_IN_LIST_PART_ERROR = 1495;
	const ER_PARTITION_ENTRY_ERROR = 1496;
	const ER_MIX_HANDLER_ERROR = 1497;
	const ER_PARTITION_NOT_DEFINED_ERROR = 1498;
	const ER_TOO_MANY_PARTITIONS_ERROR = 1499;
	const ER_SUBPARTITION_ERROR = 1500;
	const ER_CANT_CREATE_HANDLER_FILE = 1501;
	const ER_BLOB_FIELD_IN_PART_FUNC_ERROR = 1502;
	const ER_UNIQUE_KEY_NEED_ALL_FIELDS_IN_PF = 1503;
	const ER_NO_PARTS_ERROR = 1504;
	const ER_PARTITION_MGMT_ON_NONPARTITIONED = 1505;
	const ER_FOREIGN_KEY_ON_PARTITIONED = 1506;
	const ER_DROP_PARTITION_NON_EXISTENT = 1507;
	const ER_DROP_LAST_PARTITION = 1508;
	const ER_COALESCE_ONLY_ON_HASH_PARTITION = 1509;
	const ER_REORG_HASH_ONLY_ON_SAME_NO = 1510;
	const ER_REORG_NO_PARAM_ERROR = 1511;
	const ER_ONLY_ON_RANGE_LIST_PARTITION = 1512;
	const ER_ADD_PARTITION_SUBPART_ERROR = 1513;
	const ER_ADD_PARTITION_NO_NEW_PARTITION = 1514;
	const ER_COALESCE_PARTITION_NO_PARTITION = 1515;
	const ER_REORG_PARTITION_NOT_EXIST = 1516;
	const ER_SAME_NAME_PARTITION = 1517;
	const ER_NO_BINLOG_ERROR = 1518;
	const ER_CONSECUTIVE_REORG_PARTITIONS = 1519;
	const ER_REORG_OUTSIDE_RANGE = 1520;
	const ER_PARTITION_FUNCTION_FAILURE = 1521;
	const ER_PART_STATE_ERROR = 1522;
	const ER_LIMITED_PART_RANGE = 1523;
	const ER_PLUGIN_IS_NOT_LOADED = 1524;
	const ER_WRONG_VALUE = 1525;
	const ER_NO_PARTITION_FOR_GIVEN_VALUE = 1526;
	const ER_FILEGROUP_OPTION_ONLY_ONCE = 1527;
	const ER_CREATE_FILEGROUP_FAILED = 1528;
	const ER_DROP_FILEGROUP_FAILED = 1529;
	const ER_TABLESPACE_AUTO_EXTEND_ERROR = 1530;
	const ER_WRONG_SIZE_NUMBER = 1531;
	const ER_SIZE_OVERFLOW_ERROR = 1532;
	const ER_ALTER_FILEGROUP_FAILED = 1533;
	const ER_BINLOG_ROW_LOGGING_FAILED = 1534;
	const ER_BINLOG_ROW_WRONG_TABLE_DEF = 1535;
	const ER_BINLOG_ROW_RBR_TO_SBR = 1536;
	const ER_EVENT_ALREADY_EXISTS = 1537;
	const ER_EVENT_STORE_FAILED = 1538;
	const ER_EVENT_DOES_NOT_EXIST = 1539;
	const ER_EVENT_CANT_ALTER = 1540;
	const ER_EVENT_DROP_FAILED = 1541;
	const ER_EVENT_INTERVAL_NOT_POSITIVE_OR_TOO_BIG = 1542;
	const ER_EVENT_ENDS_BEFORE_STARTS = 1543;
	const ER_EVENT_EXEC_TIME_IN_THE_PAST = 1544;
	const ER_EVENT_OPEN_TABLE_FAILED = 1545;
	const ER_EVENT_NEITHER_M_EXPR_NOR_M_AT = 1546;
	const ER_COL_COUNT_DOESNT_MATCH_CORRUPTED = 1547;
	const ER_CANNOT_LOAD_FROM_TABLE = 1548;
	const ER_EVENT_CANNOT_DELETE = 1549;
	const ER_EVENT_COMPILE_ERROR = 1550;
	const ER_EVENT_SAME_NAME = 1551;
	const ER_EVENT_DATA_TOO_LONG = 1552;
	const ER_DROP_INDEX_FK = 1553;
	const ER_WARN_DEPRECATED_SYNTAX_WITH_VER = 1554;
	const ER_CANT_WRITE_LOCK_LOG_TABLE = 1555;
	const ER_CANT_LOCK_LOG_TABLE = 1556;
	const ER_FOREIGN_DUPLICATE_KEY = 1557;
	const ER_COL_COUNT_DOESNT_MATCH_PLEASE_UPDATE = 1558;
	const ER_TEMP_TABLE_PREVENTS_SWITCH_OUT_OF_RBR = 1559;
	const ER_STORED_FUNCTION_PREVENTS_SWITCH_BINLOG_FORMAT = 1560;
	const ER_NDB_CANT_SWITCH_BINLOG_FORMAT = 1561;
	const ER_PARTITION_NO_TEMPORARY = 1562;
	const ER_PARTITION_CONST_DOMAIN_ERROR = 1563;
	const ER_PARTITION_FUNCTION_IS_NOT_ALLOWED = 1564;
	const ER_DDL_LOG_ERROR = 1565;
	const ER_NULL_IN_VALUES_LESS_THAN = 1566;
	const ER_WRONG_PARTITION_NAME = 1567;
	const ER_CANT_CHANGE_TX_ISOLATION = 1568;
	const ER_DUP_ENTRY_AUTOINCREMENT_CASE = 1569;
	const ER_EVENT_MODIFY_QUEUE_ERROR = 1570;
	const ER_EVENT_SET_VAR_ERROR = 1571;
	const ER_PARTITION_MERGE_ERROR = 1572;
	const ER_CANT_ACTIVATE_LOG = 1573;
	const ER_RBR_NOT_AVAILABLE = 1574;
	const ER_BASE64_DECODE_ERROR = 1575;
	const ER_EVENT_RECURSION_FORBIDDEN = 1576;
	const ER_EVENTS_DB_ERROR = 1577;
	const ER_ONLY_INTEGERS_ALLOWED = 1578;
	const ER_UNSUPORTED_LOG_ENGINE = 1579;
	const ER_BAD_LOG_STATEMENT = 1580;
	const ER_CANT_RENAME_LOG_TABLE = 1581;
	const ER_WRONG_PARAMCOUNT_TO_NATIVE_FCT = 1582;
	const ER_WRONG_PARAMETERS_TO_NATIVE_FCT = 1583;
	const ER_WRONG_PARAMETERS_TO_STORED_FCT = 1584;
	const ER_NATIVE_FCT_NAME_COLLISION = 1585;
	const ER_DUP_ENTRY_WITH_KEY_NAME = 1586;
	const ER_BINLOG_PURGE_EMFILE = 1587;
	const ER_EVENT_CANNOT_CREATE_IN_THE_PAST = 1588;
	const ER_EVENT_CANNOT_ALTER_IN_THE_PAST = 1589;
	const ER_SLAVE_INCIDENT = 1590;
	const ER_NO_PARTITION_FOR_GIVEN_VALUE_SILENT = 1591;
	const ER_BINLOG_UNSAFE_STATEMENT = 1592;
	const ER_SLAVE_FATAL_ERROR = 1593;
	const ER_SLAVE_RELAY_LOG_READ_FAILURE = 1594;
	const ER_SLAVE_RELAY_LOG_WRITE_FAILURE = 1595;
	const ER_SLAVE_CREATE_EVENT_FAILURE = 1596;
	const ER_SLAVE_MASTER_COM_FAILURE = 1597;
	const ER_BINLOG_LOGGING_IMPOSSIBLE = 1598;
	const ER_VIEW_NO_CREATION_CTX = 1599;
	const ER_VIEW_INVALID_CREATION_CTX = 1600;
	const ER_SR_INVALID_CREATION_CTX = 1601;
	const ER_TRG_CORRUPTED_FILE = 1602;
	const ER_TRG_NO_CREATION_CTX = 1603;
	const ER_TRG_INVALID_CREATION_CTX = 1604;
	const ER_EVENT_INVALID_CREATION_CTX = 1605;
	const ER_TRG_CANT_OPEN_TABLE = 1606;
	const ER_CANT_CREATE_SROUTINE = 1607;
	const ER_NEVER_USED = 1608;
	const ER_NO_FORMAT_DESCRIPTION_EVENT_BEFORE_BINLOG_STATEMENT = 1609;
	const ER_SLAVE_CORRUPT_EVENT = 1610;
	const ER_LOAD_DATA_INVALID_COLUMN = 1611;
	const ER_LOG_PURGE_NO_FILE = 1612;
	const ER_XA_RBTIMEOUT = 1613;
	const ER_XA_RBDEADLOCK = 1614;
	const ER_NEED_REPREPARE = 1615;
	const ER_DELAYED_NOT_SUPPORTED = 1616;
	const WARN_NO_MASTER_INFO = 1617;
	const WARN_OPTION_IGNORED = 1618;
	const WARN_PLUGIN_DELETE_BUILTIN = 1619;
	const WARN_PLUGIN_BUSY = 1620;
	const ER_VARIABLE_IS_READONLY = 1621;
	const ER_WARN_ENGINE_TRANSACTION_ROLLBACK = 1622;
	const ER_SLAVE_HEARTBEAT_FAILURE = 1623;
	const ER_SLAVE_HEARTBEAT_VALUE_OUT_OF_RANGE = 1624;
	const ER_NDB_REPLICATION_SCHEMA_ERROR = 1625;
	const ER_CONFLICT_FN_PARSE_ERROR = 1626;
	const ER_EXCEPTIONS_WRITE_ERROR = 1627;
	const ER_TOO_LONG_TABLE_COMMENT = 1628;
	const ER_TOO_LONG_FIELD_COMMENT = 1629;
	const ER_FUNC_INEXISTENT_NAME_COLLISION = 1630;
	const ER_DATABASE_NAME = 1631;
	const ER_TABLE_NAME = 1632;
	const ER_PARTITION_NAME = 1633;
	const ER_SUBPARTITION_NAME = 1634;
	const ER_TEMPORARY_NAME = 1635;
	const ER_RENAMED_NAME = 1636;
	const ER_TOO_MANY_CONCURRENT_TRXS = 1637;
	const WARN_NON_ASCII_SEPARATOR_NOT_IMPLEMENTED = 1638;
	const ER_DEBUG_SYNC_TIMEOUT = 1639;
	const ER_DEBUG_SYNC_HIT_LIMIT = 1640;
	const ER_DUP_SIGNAL_SET = 1641;
	const ER_SIGNAL_WARN = 1642;
	const ER_SIGNAL_NOT_FOUND = 1643;
	const ER_SIGNAL_EXCEPTION = 1644;
	const ER_RESIGNAL_WITHOUT_ACTIVE_HANDLER = 1645;
	const ER_SIGNAL_BAD_CONDITION_TYPE = 1646;
	const WARN_COND_ITEM_TRUNCATED = 1647;
	const ER_COND_ITEM_TOO_LONG = 1648;
	const ER_UNKNOWN_LOCALE = 1649;
	const ER_SLAVE_IGNORE_SERVER_IDS = 1650;
	const ER_QUERY_CACHE_DISABLED = 1651;
	const ER_SAME_NAME_PARTITION_FIELD = 1652;
	const ER_PARTITION_COLUMN_LIST_ERROR = 1653;
	const ER_WRONG_TYPE_COLUMN_VALUE_ERROR = 1654;
	const ER_TOO_MANY_PARTITION_FUNC_FIELDS_ERROR = 1655;
	const ER_MAXVALUE_IN_VALUES_IN = 1656;
	const ER_TOO_MANY_VALUES_ERROR = 1657;
	const ER_ROW_SINGLE_PARTITION_FIELD_ERROR = 1658;
	const ER_FIELD_TYPE_NOT_ALLOWED_AS_PARTITION_FIELD = 1659;
	const ER_PARTITION_FIELDS_TOO_LONG = 1660;
	const ER_BINLOG_ROW_ENGINE_AND_STMT_ENGINE = 1661;
	const ER_BINLOG_ROW_MODE_AND_STMT_ENGINE = 1662;
	const ER_BINLOG_UNSAFE_AND_STMT_ENGINE = 1663;
	const ER_BINLOG_ROW_INJECTION_AND_STMT_ENGINE = 1664;
	const ER_BINLOG_STMT_MODE_AND_ROW_ENGINE = 1665;
	const ER_BINLOG_ROW_INJECTION_AND_STMT_MODE = 1666;
	const ER_BINLOG_MULTIPLE_ENGINES_AND_SELF_LOGGING_ENGINE = 1667;
	const ER_BINLOG_UNSAFE_LIMIT = 1668;
	const ER_BINLOG_UNSAFE_INSERT_DELAYED = 1669;
	const ER_BINLOG_UNSAFE_SYSTEM_TABLE = 1670;
	const ER_BINLOG_UNSAFE_AUTOINC_COLUMNS = 1671;
	const ER_BINLOG_UNSAFE_UDF = 1672;
	const ER_BINLOG_UNSAFE_SYSTEM_VARIABLE = 1673;
	const ER_BINLOG_UNSAFE_SYSTEM_FUNCTION = 1674;
	const ER_BINLOG_UNSAFE_NONTRANS_AFTER_TRANS = 1675;
	const ER_MESSAGE_AND_STATEMENT = 1676;
	const ER_SLAVE_CONVERSION_FAILED = 1677;
	const ER_SLAVE_CANT_CREATE_CONVERSION = 1678;
	const ER_INSIDE_TRANSACTION_PREVENTS_SWITCH_BINLOG_FORMAT = 1679;
	const ER_PATH_LENGTH = 1680;
	const ER_WARN_DEPRECATED_SYNTAX_NO_REPLACEMENT = 1681;
	const ER_WRONG_NATIVE_TABLE_STRUCTURE = 1682;
	const ER_WRONG_PERFSCHEMA_USAGE = 1683;
	const ER_WARN_I_S_SKIPPED_TABLE = 1684;
	const ER_INSIDE_TRANSACTION_PREVENTS_SWITCH_BINLOG_DIRECT = 1685;
	const ER_STORED_FUNCTION_PREVENTS_SWITCH_BINLOG_DIRECT = 1686;
	const ER_SPATIAL_MUST_HAVE_GEOM_COL = 1687;
	const ER_TOO_LONG_INDEX_COMMENT = 1688;
	const ER_LOCK_ABORTED = 1689;
	const ER_DATA_OUT_OF_RANGE = 1690;
	const ER_WRONG_SPVAR_TYPE_IN_LIMIT = 1691;
	const ER_BINLOG_UNSAFE_MULTIPLE_ENGINES_AND_SELF_LOGGING_ENGINE = 1692;
	const ER_BINLOG_UNSAFE_MIXED_STATEMENT = 1693;
	const ER_INSIDE_TRANSACTION_PREVENTS_SWITCH_SQL_LOG_BIN = 1694;
	const ER_STORED_FUNCTION_PREVENTS_SWITCH_SQL_LOG_BIN = 1695;
	const ER_FAILED_READ_FROM_PAR_FILE = 1696;
	const ER_VALUES_IS_NOT_INT_TYPE_ERROR = 1697;
	const ER_ACCESS_DENIED_NO_PASSWORD_ERROR = 1698;
	const ER_SET_PASSWORD_AUTH_PLUGIN = 1699;
	const ER_GRANT_PLUGIN_USER_EXISTS = 1700;
	const ER_TRUNCATE_ILLEGAL_FK = 1701;
	const ER_PLUGIN_IS_PERMANENT = 1702;
	const ER_SLAVE_HEARTBEAT_VALUE_OUT_OF_RANGE_MIN = 1703;
	const ER_SLAVE_HEARTBEAT_VALUE_OUT_OF_RANGE_MAX = 1704;
	const ER_STMT_CACHE_FULL = 1705;
	const ER_MULTI_UPDATE_KEY_CONFLICT = 1706;
	const ER_TABLE_NEEDS_REBUILD = 1707;
	const WARN_OPTION_BELOW_LIMIT = 1708;
	const ER_INDEX_COLUMN_TOO_LONG = 1709;
	const ER_ERROR_IN_TRIGGER_BODY = 1710;
	const ER_ERROR_IN_UNKNOWN_TRIGGER_BODY = 1711;
	const ER_INDEX_CORRUPT = 1712;
	const ER_UNDO_RECORD_TOO_BIG = 1713;
	const ER_BINLOG_UNSAFE_INSERT_IGNORE_SELECT = 1714;
	const ER_BINLOG_UNSAFE_INSERT_SELECT_UPDATE = 1715;
	const ER_BINLOG_UNSAFE_REPLACE_SELECT = 1716;
	const ER_BINLOG_UNSAFE_CREATE_IGNORE_SELECT = 1717;
	const ER_BINLOG_UNSAFE_CREATE_REPLACE_SELECT = 1718;
	const ER_BINLOG_UNSAFE_UPDATE_IGNORE = 1719;
	const ER_PLUGIN_NO_UNINSTALL = 1720;
	const ER_PLUGIN_NO_INSTALL = 1721;
	const ER_BINLOG_UNSAFE_WRITE_AUTOINC_SELECT = 1722;
	const ER_BINLOG_UNSAFE_CREATE_SELECT_AUTOINC = 1723;
	const ER_BINLOG_UNSAFE_INSERT_TWO_KEYS = 1724;
	const ER_TABLE_IN_FK_CHECK = 1725;
	const ER_UNSUPPORTED_ENGINE = 1726;
	const ER_BINLOG_UNSAFE_AUTOINC_NOT_FIRST = 1727;

	//------------------------------------------------------------------------------ CR_UNKNOWN_ERROR
	const CR_UNKNOWN_ERROR = 2000;
	const CR_SOCKET_CREATE_ERROR = 2001;
	const CR_CONNECTION_ERROR = 2002;
	const CR_CONN_HOST_ERROR = 2003;
	const CR_IPSOCK_ERROR = 2004;
	const CR_UNKNOWN_HOST = 2005;
	const CR_SERVER_GONE_ERROR = 2006;
	const CR_VERSION_ERROR = 2007;
	const CR_OUT_OF_MEMORY = 2008;
	const CR_WRONG_HOST_INFO = 2009;
	const CR_LOCALHOST_CONNECTION = 2010;
	const CR_TCP_CONNECTION = 2011;
	const CR_SERVER_HANDSHAKE_ERR = 2012;
	const CR_SERVER_LOST = 2013;
	const CR_COMMANDS_OUT_OF_SYNC = 2014;
	const CR_NAMEDPIPE_CONNECTION = 2015;
	const CR_NAMEDPIPEWAIT_ERROR = 2016;
	const CR_NAMEDPIPEOPEN_ERROR = 2017;
	const CR_NAMEDPIPESETSTATE_ERROR = 2018;
	const CR_CANT_READ_CHARSET = 2019;
	const CR_NET_PACKET_TOO_LARGE = 2020;
	const CR_EMBEDDED_CONNECTION = 2021;
	const CR_PROBE_SLAVE_STATUS = 2022;
	const CR_PROBE_SLAVE_HOSTS = 2023;
	const CR_PROBE_SLAVE_CONNECT = 2024;
	const CR_PROBE_MASTER_CONNECT = 2025;
	const CR_SSL_CONNECTION_ERROR = 2026;
	const CR_MALFORMED_PACKET = 2027;
	const CR_WRONG_LICENSE = 2028;
	const CR_NULL_POINTER = 2029;
	const CR_NO_PREPARE_STMT = 2030;
	const CR_PARAMS_NOT_BOUND = 2031;
	const CR_DATA_TRUNCATED = 2032;
	const CR_NO_PARAMETERS_EXISTS = 2033;
	const CR_INVALID_PARAMETER_NO = 2034;
	const CR_INVALID_BUFFER_USE = 2035;
	const CR_UNSUPPORTED_PARAM_TYPE = 2036;
	const CR_SHARED_MEMORY_CONNECTION = 2037;
	const CR_SHARED_MEMORY_CONNECT_REQUEST_ERROR = 2038;
	const CR_SHARED_MEMORY_CONNECT_ANSWER_ERROR = 2039;
	const CR_SHARED_MEMORY_CONNECT_FILE_MAP_ERROR = 2040;
	const CR_SHARED_MEMORY_CONNECT_MAP_ERROR = 2041;
	const CR_SHARED_MEMORY_FILE_MAP_ERROR = 2042;
	const CR_SHARED_MEMORY_MAP_ERROR = 2043;
	const CR_SHARED_MEMORY_EVENT_ERROR = 2044;
	const CR_SHARED_MEMORY_CONNECT_ABANDONED_ERROR = 2045;
	const CR_SHARED_MEMORY_CONNECT_SET_ERROR = 2046;
	const CR_CONN_UNKNOW_PROTOCOL = 2047;
	const CR_INVALID_CONN_HANDLE = 2048;
	const CR_SECURE_AUTH = 2049;
	const CR_UNUSED_1 = 2049;
	const CR_FETCH_CANCELED = 2050;
	const CR_NO_DATA = 2051;
	const CR_NO_STMT_METADATA = 2052;
	const CR_NO_RESULT_SET = 2053;
	const CR_NOT_IMPLEMENTED = 2054;
	const CR_SERVER_LOST_EXTENDED = 2055;
	const CR_STMT_CLOSED = 2056;
	const CR_NEW_STMT_METADATA = 2057;
	const CR_ALREADY_CONNECTED = 2058;
	const CR_AUTH_PLUGIN_CANNOT_LOAD = 2059;
	const CR_DUPLICATE_CONNECTION_ATTR = 2060;
	const CR_AUTH_PLUGIN_ERR = 2061;
	const CR_INSECURE_API_ERR = 2062;

	//------------------------------------------------------------------------------ ER_QUERY_TIMEOUT
	const ER_QUERY_TIMEOUT = 3024;

}
