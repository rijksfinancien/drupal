<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api_public\Controller;

use Drupal\minfin_api\Controller\BaseApiController;

/**
 * The base API.
 *
 * @SWG\Swagger(
 *   basePath="/",
 * @SWG\Info(
 *     version="1.0.0",
 *     title="Rijksfinancien: Open API",
 *     description="This page contains a description of the API calls used to generate the visualisation on the homepage. By clicking on the call you can get more details about the required parameters and the return values. With the 'Try it out' button you can also test the call yourself.",
 *   )
 * )
 *
 * @SWG\Parameter(
 *   parameter = "yearPath",
 *   name = "year",
 *   in = "path",
 *   required = true,
 *   type = "integer",
 *   description = "The calendar year.",
 * );
 *
 * @SWG\Parameter(
 *   parameter = "yearQuery",
 *   name = "year",
 *   in = "query",
 *   type = "integer",
 *   description = "The calendar year.",
 * );
 *
 * @SWG\Parameter(
 *   parameter = "phasePath",
 *   name = "phase",
 *   in = "path",
 *   required = true,
 *   type = "string",
 *   enum={"JV", "OWB"},
 *   description = "The phase you want to receive data for.<br />Use 'JV' to get the data for the 'year report'.<br />Use 'OWB' to get the data for the the 'Annual budget'.",
 * );
 *
 * @SWG\Parameter(
 *   parameter = "phaseQuery",
 *   name = "phase",
 *   in = "query",
 *   type = "string",
 *   enum={"JV", "OWB"},
 *   description = "The phase you want to receive data for.<br />Use 'JV' to get the data for the 'year report'.<br />Use 'OWB' to get the data for the the 'Annual budget'.",
 * );
 *
 * @SWG\Response(
 *   response = "success",
 *   description = "Successful call.<br />The returned json array will contain some base information with all the requested values inside the children element. If a child element has an identifier this value can be used in the next subsequent call to retrieve all the children for the given element.",
 * );
 *
 * @SWG\Response(
 *   response = "failure",
 *   description = "No data found for the requested parameters.",
 * )
 */
class BasePublicApiController extends BaseApiController {

}
