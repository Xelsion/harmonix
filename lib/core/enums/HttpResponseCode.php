<?php

namespace lib\core\enums;

enum HttpResponseCode: int {

	case Ok = 200;
	case Created = 201;
	case Accepted = 202;
	case NoAuthoritativeInformation = 203;
	case NoContent = 204;
	case ResetContent = 205;
	case PartialContent = 206;
	case MultiStatus = 207;
	case AlreadyReported = 208;
	case IMUsed = 209;
	case MultipleChoices = 300;
	case MovedPermanently = 301;
	case Found = 302;
	case SeeOther = 303;
	case NotModified = 304;
	case TemporaryRedirect = 307;
	case PermanentRedirect = 308;
	case BadRequest = 400;
	case Unauthorized = 401;
	case PaymentRequired = 402;
	case Forbidden = 403;
	case NotFound = 404;
	case MethodNotAllowed = 405;
	case NotAcceptable = 406;
	case ProxyAuthenticationRequired = 407;
	case RequestTimeout = 408;
	case Conflict = 409;
	case Gone = 410;
	case LengthRequired = 411;
	case PreconditionFailed = 412;
	case PayloadToLarge = 413;
	case URIToLarge = 414;
	case UnsupportedMediaType = 415;
	case RangeNotSatisfiable = 416;
	case ExpectationFailed = 417;
	case IMaTeapot = 418;
	case MisdirectedRequest = 421;
	case UnprocessableContent = 422;
	case Locked = 423;
	case FailedDependency = 424;
	case TooEarly = 425;
	case UpgradeRequired = 426;
	case PreconditionRequired = 428;
	case TooManyRequests = 429;
	case RequestHeaderFieldsTooLarge = 431;
	case UnavailableForLegalReason = 451;
	case InternalServerError = 500;
	case NotImplemented = 501;
	case BadGateway = 502;
	case ServiceUnavailable = 503;
	case GatewayTimeout = 504;
	case HttpVersionNotSupported = 505;
	case VariantAlsoNegotiates = 506;
	case InsufficientStorage = 507;
	case LoopDetected = 508;
	case NotExtended = 510;
	case NetworkAuthenticationRequired = 511;

	/**
	 * Returns the status code as string
	 *
	 * @return string
	 */
	public function toString(): string {
		$protocol = $_SERVER["SERVER_PROTOCOL"] ?? "HTTP/1.1";
		return match ($this) {
			self::Ok => "{$protocol} 200 Ok",
			self::Created => "{$protocol} 201 Created",
			self::Accepted => "{$protocol} 202 Accepted",
			self::NoAuthoritativeInformation => "{$protocol} 203 No Authoritative Information",
			self::NoContent => "{$protocol} 204 No Content",
			self::ResetContent => "{$protocol} 205 Reset Content",
			self::PartialContent => "{$protocol} 206 Partial Content",
			self::MultiStatus => "{$protocol} 207 Multi Status",
			self::AlreadyReported => "{$protocol} 208 Already Reported",
			self::IMUsed => "{$protocol} 209 I'm Used",
			self::MultipleChoices => "{$protocol} 300 Multiple Choices",
			self::MovedPermanently => "{$protocol} 301 Moved Permanently",
			self::Found => "{$protocol} 302 Found",
			self::SeeOther => "{$protocol} 303 See Other",
			self::NotModified => "{$protocol} 304 Not Modified",
			self::TemporaryRedirect => "{$protocol} 307 Temporary Redirect",
			self::PermanentRedirect => "{$protocol} 308 Permanent Redirect",
			self::BadRequest => "{$protocol} 400 Bad Request",
			self::Unauthorized => "{$protocol} 401 Unauthorized",
			self::PaymentRequired => "{$protocol} 402 Payment Required",
			self::Forbidden => "{$protocol} 403 Forbidden",
			self::NotFound => "{$protocol} 404 Not Found",
			self::MethodNotAllowed => "{$protocol} 405 Method not Allowed",
			self::NotAcceptable => "{$protocol} 406 Not Acceptable",
			self::ProxyAuthenticationRequired => "{$protocol} 407 Proxy Authentication Required",
			self::RequestTimeout => "{$protocol} 408 Request Timeout",
			self::Conflict => "{$protocol} 409 Conflict",
			self::Gone => "{$protocol} 410 Gone",
			self::LengthRequired => "{$protocol} 411 Length Required",
			self::PreconditionFailed => "{$protocol} 412 Precondition Failed",
			self::PayloadToLarge => "{$protocol} 413 Payload to Large",
			self::URIToLarge => "{$protocol} 414 URI to Large",
			self::UnsupportedMediaType => "{$protocol} 415 Unsupported Media Type",
			self::RangeNotSatisfiable => "{$protocol} 416 Range not Satisfiable",
			self::ExpectationFailed => "{$protocol} 417 Expectation Failed",
			self::IMaTeapot => "{$protocol} 418 I'm a Teapot",
			self::MisdirectedRequest => "{$protocol} 421 Misdirected Request",
			self::UnprocessableContent => "{$protocol} 422 Unprocessable Content",
			self::Locked => "{$protocol} 423 Locked",
			self::FailedDependency => "{$protocol} 424 Failed Dependency",
			self::TooEarly => "{$protocol} 425 Too Early",
			self::UpgradeRequired => "{$protocol} 426 Upgrade Required",
			self::PreconditionRequired => "{$protocol} 428 Precondition Required",
			self::TooManyRequests => "{$protocol} 429 Too Many Requests",
			self::RequestHeaderFieldsTooLarge => "{$protocol} 431 Request Header-Fields too Large",
			self::UnavailableForLegalReason => "{$protocol} 451 Unavailable for Legal Reason",
			self::InternalServerError => "{$protocol} 500 Internal Server Error",
			self::NotImplemented => "{$protocol} 501 Not Implemented",
			self::BadGateway => "{$protocol} 502 Bad Gateway",
			self::ServiceUnavailable => "{$protocol} 503 Service Unavailable",
			self::GatewayTimeout => "{$protocol} 504 Gateway Timeout",
			self::HttpVersionNotSupported => "{$protocol} 505 Http-Version not Supported",
			self::VariantAlsoNegotiates => "{$protocol} 506 Variant also Negotiates",
			self::InsufficientStorage => "{$protocol} 507 Insufficient Storage",
			self::LoopDetected => "{$protocol} 508 Loop detected",
			self::NotExtended => "{$protocol} 510 Not Extended",
			self::NetworkAuthenticationRequired => "{$protocol} 511 Network Authentication Required"
		};
	}
}