<?php
	final class WebMoneyUtilsTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * @dataProvider data
		**/
		public function testHashValidation(
			$data, $secretKey, $expectedHash, $assertFunction
		)
		{
			$this->{$assertFunction}(
				WebMoneyUtils::isValidPayment(
					$expectedHash,
					WebMoneyUtils::makePaymentHash($secretKey, $data)
				)
			);
		}

		public static function data()
		{
			return array(
				array(
					array(
						'LMI_MODE'				=> '1',
						'LMI_PAYMENT_AMOUNT'	=> '20.00',
						'LMI_PAYEE_PURSE'		=> 'R334500596670',
						'LMI_PAYMENT_NO'		=> '1234',
						'LMI_PAYER_WM'			=> '370327567925',
						'LMI_PAYER_PURSE'		=> 'R334500596670',
						'LMI_SYS_INVS_NO'		=> '866',
						'LMI_SYS_TRANS_NO'		=> '407',
						'LMI_SYS_TRANS_DATE'	=> '20090715 17:06:32',
						'LMI_HASH'				=> '50D04367E2D6BC175A1F9ED46F336246'
					),
					'test',
					'50D04367E2D6BC175A1F9ED46F336246',
					'assertTrue'
				),
				array(
					array(
						'LMI_MODE'				=> '1',
						'LMI_PAYMENT_AMOUNT'	=> '20.00',
						'LMI_PAYEE_PURSE'		=> 'R334500596670',
						'LMI_PAYMENT_NO'		=> '1234',
						'LMI_PAYER_WM'			=> '370327567925',
						'LMI_PAYER_PURSE'		=> 'R334500596670',
						'LMI_SYS_INVS_NO'		=> '866',
						'LMI_SYS_TRANS_NO'		=> '407',
						'LMI_SYS_TRANS_DATE'	=> '20090715 17:06:32',
						'LMI_HASH'				=> '50D04367E2D6BC175A1F9ED46F336246'
					),
					'test1',
					'50D04367E2D6BC175A1F9ED46F336246',
					'assertFalse'
				),
				array(
					array(
						'LMI_MODE'				=> '1',
						'LMI_PAYMENT_AMOUNT'	=> '99.00',
						'LMI_PAYEE_PURSE'		=> 'R334500596670',
						'LMI_PAYMENT_NO'		=> '1234',
						'LMI_PAYER_WM'			=> '370327567925',
						'LMI_PAYER_PURSE'		=> 'R334500596670',
						'LMI_SYS_INVS_NO'		=> '866',
						'LMI_SYS_TRANS_NO'		=> '407',
						'LMI_SYS_TRANS_DATE'	=> '20090715 17:06:32',
						'LMI_HASH'				=> '50D04367E2D6BC175A1F9ED46F336246'
					),
					'test',
					'50D04367E2D6BC175A1F9ED46F336246',
					'assertFalse'
				),
			);
		}
	}
?>
