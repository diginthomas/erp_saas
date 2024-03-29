/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */
let idCount = 0

/**
 * Dead simple unique ID implementation.
 * Thanks lodash!
 * @return {number}
 */
function uniqueId() {
  return ++idCount
}

export default uniqueId
