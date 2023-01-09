<?php

namespace Drupal\napcs_job_board\Plugin\Block;

// Use Drupal\Core\Block\BlockBase;.
use Drupal\napcs_job_board\NJBBlockBase;
use Drupal\napcs_job_board\NJBHookHelpers as Helper;

/**
 * Provides an Organization Content Block.
 *
 * @Block(
 *   id = "njb_organization_block",
 *   admin_label = @Translation("Organization Content"),
 *   category = @Translation("Organization"),
 * )
 */
class NJBOrganizationBlock extends NJBBlockBase {

  protected $orgReqName = 'org';
  protected $orgId;
  protected $org;
  protected $orgName;
  protected $orgDesc;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $id = $this->getRequestValue($this->orgReqName);
    if (!empty($id)) {
      $this->setOrgId($id);
      $this->setOrgContent($id);
      if (!empty($this->org)) {
        $this->setOrgName();
        $this->setOrgDesc();
      }
    }

    return [
      '#theme' => 'njb_organization_block',
      '#title' => '',
      '#markup' => 'Nothin',
      '#org' => [
        'org_description' => $this->orgDesc,
        'org_id' => $this->orgId,
        'org_name' => $this->orgName,
      ],
      '#cache' => ['contexts' => ['url.query_args']],
    ];
  }

  /**
   * Populate org variable with org node object.
   */
  private function setOrgContent(string $id) {
    $this->org = Helper::nload($id);
  }

  /**
   * Set the org node id.
   */
  private function setOrgId($id) {
    $this->orgId = $id;
  }

  /**
   * Set the org name.
   */
  private function setOrgName() {
    if (!empty($this->org)) {
      $this->orgName = $this->org->title->value;
    }
  }

  /**
   * Set the org description.
   */
  private function setOrgDesc() {
    if (!empty($this->org)) {
      $this->orgDesc = $this->org->body->value;
    }
  }

}
