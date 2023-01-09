<?php

namespace Drupal\napcs_job_board;

/**
 * Useful route names for the NAPCS job board.
 */
class NJBRoute {

  /**
   * The route name of the job listings view page.
   *
   * @var string
   */
  const LISTINGS = 'view.napcs_job_listings.page_1';

  /**
   * The route name of the job board register page.
   *
   * @var string
   */
  const REGISTER = 'napcs_job_board.user_register_form';

  /**
   * The route name of the job board login page.
   *
   * @var string
   */
  const LOGIN = 'napcs_job_board.user_login';

  /**
   * The route name of the job board password reset page.
   *
   * @var string
   */
  const PASSWORD = 'napcs_job_board.user_password';

}
