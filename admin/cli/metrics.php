<?php
/**
 * Rotina de captura de mÃ©tricas basicas gerenciais do Moodle.
 * Desenvolvido pela equipe da empresa BlackBean Ltda e utilizada
 * por ela para o monitoramento e provisionamento preventivo.
 *
 * @package BlackBeam Monitoring Service - Moodle Plugin
 * @author Bruno Magalhaes <brunomagalhaes@blackbean.com.br>
 * @version 1.0
 */
/**
 * 
 */
define('CLI_SCRIPT', true);

/**
 * 
 */
require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');

/**
 * 
 */
$metrics = new stdclass();
$metrics->users = new stdclass();
$metrics->courses = new stdclass();
$metrics->sections = new stdclass();
$metrics->categories = new stdclass();
//$metrics->files = new stdclass();
//$metrics->logs = new stdclass();

/**
 * 
 */
$metrics->users->active = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
													  "FROM {user} AS tb1 ".
													  "WHERE tb1.suspended = 0 ".
													  "AND tb1.deleted = 0");

/**
 * 
 */
$metrics->users->online = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
													  "FROM {user} AS tb1 ".
													  "WHERE tb1.lastaccess >= (UNIX_TIMESTAMP() - 300) ".
													  "AND tb1.suspended = 0 ".
													  "AND tb1.deleted = 0");

/**
 * 
 */
$metrics->users->enrolled = (integer)$DB->get_field_sql("SELECT COUNT(DISTINCT tb1.id) ".
														"FROM {user} AS tb1 ".
														"INNER JOIN {user_enrolments} AS tb2 ".
														"ON tb2.userid = tb1.id ".
														"INNER JOIN {enrol} AS tb3 ".
														"ON tb3.id = tb2.enrolid ".
														"INNER JOIN {course} AS tb4 ".
														"ON tb3.courseid = tb4.id ".
														"WHERE tb1.suspended = 0 ".
														"AND tb1.deleted = 0 ".
														"AND tb3.status = 0 ".
														"AND (tb2.timeend = 0 OR tb2.timeend > NOW()) ".
														"AND tb2.status = 0 ".
														"AND tb4.visible = 1");

/**
 * 
 */
$metrics->users->suspended = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
														 "FROM {user} AS tb1 ".
														 "WHERE tb1.suspended = 1 ".
														 "AND tb1.deleted = 0");

/**
 * 
 */
$metrics->users->deleted = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
													   "FROM {user} AS tb1 ".
													   "WHERE tb1.deleted = 1");

/**
 * 
 */
$metrics->courses->visible = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
														 "FROM {course} AS tb1 ".
														 "WHERE tb1.visible = 1");

/**
 * 
 */
$metrics->courses->invisible = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
														   "FROM {course} AS tb1 ".
														   "WHERE tb1.visible = 0");

/**
 * 
 */
$metrics->sections->visible = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
															"FROM {course_sections} AS tb1 ".
															"WHERE tb1.visible = 1");

/**
 * 
 */
$metrics->sections->invisible = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
															  "FROM {course_sections} AS tb1 ".
															  "WHERE tb1.visible = 0");

/**
 * 
 */
$metrics->categories->visible = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
															"FROM {course_categories} AS tb1 ".
															"WHERE tb1.visible = 1");

/**
 * 
 */
$metrics->categories->invisible = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
															  "FROM {course_categories} AS tb1 ".
															  "WHERE tb1.visible = 0");

/**
 * 
 */
//$metrics->files->count = (integer)$DB->get_field_sql("SELECT COUNT(tb1.id) ".
//													 "FROM {files} AS tb1 ".
//													 "WHERE tb1.filename <> '.' ".
//													 "AND tb1.filesize <> 0 ".
//													 "GROUP BY tb1.contenthash");

/**
 * 
 */
//$metrics->files->size = (integer)$DB->get_field_sql("SELECT SUM(tb2.filesize) ".
//													"FROM (SELECT tb1.filesize ".
//														 "FROM {files} AS tb1 ".
//														 "WHERE tb1.filename <> '.' ".
//														 "AND tb1.filesize <> 0 ".
//														 "GROUP BY tb1.contenthash) AS tb2");

/**
 * 
 */
echo(json_encode($metrics));
