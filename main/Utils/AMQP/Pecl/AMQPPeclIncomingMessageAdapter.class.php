<?php
/***************************************************************************
 *   Copyright (C) 2012 by Evgeniya Tekalin                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace Onphp;

final class AMQPPeclIncomingMessageAdapter extends StaticFactory
{
	/**
	 * @param \AMQPEnvelope $incoming
	 * @return \Onphp\AMQPIncomingMessage
	 */
	public static function convert(\AMQPEnvelope $incoming)
	{
		$data = array(
			AMQPIncomingMessage::APP_ID => $incoming->getAppId(),
			AMQPIncomingMessage::BODY => $incoming->getBody(),
			AMQPIncomingMessage::CONTENT_ENCODING => $incoming->getContentEncoding(),
			AMQPIncomingMessage::CONTENT_TYPE => $incoming->getContentType(),
			AMQPIncomingMessage::CORRELATION_ID => $incoming->getCorrelationId(),
			//AMQPIncomingMessage::COUNT => $incoming->getCount(),
			//AMQPIncomingMessage::CONSUME_BODY => $incoming->getConsumeBody(),
			//AMQPIncomingMessage::CONSUMER_TAG => $incoming->getConsumeTagName(),
			AMQPIncomingMessage::DELIVERY_TAG => $incoming->getDeliveryTag(),
			AMQPIncomingMessage::DELIVERY_MODE => $incoming->getDeliveryMode(),
			AMQPIncomingMessage::EXCHANGE => $incoming->getExchangeName(),
			AMQPIncomingMessage::EXPIRATION => $incoming->getExpiration(),
			AMQPIncomingMessage::MESSAGE_ID => $incoming->getMessageId(),
			AMQPIncomingMessage::PRIORITY => $incoming->getPriority(),
			AMQPIncomingMessage::REPLY_TO => $incoming->getReplyTo(),
			AMQPIncomingMessage::REDELIVERED => $incoming->isRedelivery(),
			AMQPIncomingMessage::PRIORITY => $incoming->getPriority(),
			AMQPIncomingMessage::ROUTING_KEY => $incoming->getRoutingKey(),
			AMQPIncomingMessage::TIMESTAMP => $incoming->getTimeStamp(),
			AMQPIncomingMessage::TYPE => $incoming->getType(),
			AMQPIncomingMessage::USER_ID => $incoming->getUserId()
		);

		return AMQPIncomingMessage::spawn($data);
	}

}

?>