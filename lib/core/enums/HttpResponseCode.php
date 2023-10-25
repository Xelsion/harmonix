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

	public static function fromCode($code): HttpResponseCode {
		return match ($code) {
			200 => self::Ok,
			201 => self::Created,
			202 => self::Accepted,
			203 => self::NoAuthoritativeInformation,
			204 => self::NoContent,
			205 => self::ResetContent,
			206 => self::PartialContent,
			207 => self::MultiStatus,
			208 => self::AlreadyReported,
			209 => self::IMUsed,
			300 => self::MultipleChoices,
			301 => self::MovedPermanently,
			302 => self::Found,
			303 => self::SeeOther,
			304 => self::NotModified,
			307 => self::TemporaryRedirect,
			308 => self::PermanentRedirect,
			400 => self::BadRequest,
			401 => self::Unauthorized,
			402 => self::PaymentRequired,
			403 => self::Forbidden,
			404 => self::NotFound,
			405 => self::MethodNotAllowed,
			406 => self::NotAcceptable,
			407 => self::ProxyAuthenticationRequired,
			408 => self::RequestTimeout,
			409 => self::Conflict,
			410 => self::Gone,
			411 => self::LengthRequired,
			412 => self::PreconditionFailed,
			413 => self::PayloadToLarge,
			414 => self::URIToLarge,
			415 => self::UnsupportedMediaType,
			416 => self::RangeNotSatisfiable,
			417 => self::ExpectationFailed,
			418 => self::IMaTeapot,
			421 => self::MisdirectedRequest,
			422 => self::UnprocessableContent,
			423 => self::Locked,
			424 => self::FailedDependency,
			425 => self::TooEarly,
			426 => self::UpgradeRequired,
			428 => self::PreconditionRequired,
			429 => self::TooManyRequests,
			431 => self::RequestHeaderFieldsTooLarge,
			451 => self::UnavailableForLegalReason,
			500 => self::InternalServerError,
			501 => self::NotImplemented,
			502 => self::BadGateway,
			503 => self::ServiceUnavailable,
			504 => self::GatewayTimeout,
			505 => self::HttpVersionNotSupported,
			506 => self::VariantAlsoNegotiates,
			507 => self::InsufficientStorage,
			508 => self::LoopDetected,
			510 => self::NotExtended,
			511 => self::NetworkAuthenticationRequired
		};
	}

	public function toString(): string {
		return match ($this) {
			self::Ok => "HTTP/1.1 200 Ok",
			self::Created => "HTTP/1.1 201 Created",
			self::Accepted => "HTTP/1.1 202 Accepted",
			self::NoAuthoritativeInformation => "HTTP/1.1 203 No Authoritative Information",
			self::NoContent => "HTTP/1.1 204 No Content",
			self::ResetContent => "HTTP/1.1 205 Reset Content",
			self::PartialContent => "HTTP/1.1 206 Partial Content",
			self::MultiStatus => "HTTP/1.1 207 Multi Status",
			self::AlreadyReported => "HTTP/1.1 208 Already Reported",
			self::IMUsed => "HTTP/1.1 209 I'm Used",
			self::MultipleChoices => "HTTP/1.1 300 Multiple Choices",
			self::MovedPermanently => "HTTP/1.1 301Moved Permanently",
			self::Found => "HTTP/1.1 302 Found",
			self::SeeOther => "HTTP/1.1 303 See Other",
			self::NotModified => "HTTP/1.1 304 Not Modified",
			self::TemporaryRedirect => "HTTP/1.1 307 Temporary Redirect",
			self::PermanentRedirect => "HTTP/1.1 308 Permanent Redirect",
			self::BadRequest => "HTTP/1.1 400 Bad Request",
			self::Unauthorized => "HTTP/1.1 401 Unauthorized",
			self::PaymentRequired => "HTTP/1.1 402 Payment Required",
			self::Forbidden => "HTTP/1.1 403 Forbidden",
			self::NotFound => "HTTP/1.1 404 Not Found",
			self::MethodNotAllowed => "HTTP/1.1 405 Method not Allowed",
			self::NotAcceptable => "HTTP/1.1 406 Not Acceptable",
			self::ProxyAuthenticationRequired => "HTTP/1.1 407 Proxy Authentication Required",
			self::RequestTimeout => "HTTP/1.1 408 Request Timeout",
			self::Conflict => "HTTP/1.1 409 Conflict",
			self::Gone => "HTTP/1.1 410 Gone",
			self::LengthRequired => "HTTP/1.1 411 Length Required",
			self::PreconditionFailed => "HTTP/1.1 412 Precondition Failed",
			self::PayloadToLarge => "HTTP/1.1 413 Payload to Large",
			self::URIToLarge => "HTTP/1.1 414 URI to Large",
			self::UnsupportedMediaType => "HTTP/1.1 415 Unsupported Media Type",
			self::RangeNotSatisfiable => "HTTP/1.1 416 Range not Satisfiable",
			self::ExpectationFailed => "HTTP/1.1 417 Expectation Failed",
			self::IMaTeapot => "HTTP/1.1 418 I'm a Teapot",
			self::MisdirectedRequest => "HTTP/1.1 421 Misdirected Request",
			self::UnprocessableContent => "HTTP/1.1 422 Unprocessable Content",
			self::Locked => "HTTP/1.1 423 Locked",
			self::FailedDependency => "HTTP/1.1 424 Failed Dependency",
			self::TooEarly => "HTTP/1.1 425 Too Early",
			self::UpgradeRequired => "HTTP/1.1 426 Upgrade Required",
			self::PreconditionRequired => "HTTP/1.1 428 Precondition Required",
			self::TooManyRequests => "HTTP/1.1 429 Too Many Requests",
			self::RequestHeaderFieldsTooLarge => "HTTP/1.1 431 Request Header-Fields too Large",
			self::UnavailableForLegalReason => "HTTP/1.1 451 Unavailable for Legal Reason",
			self::InternalServerError => "HTTP/1.1 500 Internal Server Error",
			self::NotImplemented => "HTTP/1.1 501 Not Implemented",
			self::BadGateway => "HTTP/1.1 502 Bad Gateway",
			self::ServiceUnavailable => "HTTP/1.1 503 Service Unavailable",
			self::GatewayTimeout => "HTTP/1.1 504 Gateway Timeout",
			self::HttpVersionNotSupported => "HTTP/1.1 505 Http-Version not Supported",
			self::VariantAlsoNegotiates => "HTTP/1.1 506 Variant also Negotiates",
			self::InsufficientStorage => "HTTP/1.1 507 Insufficient Storage",
			self::LoopDetected => "HTTP/1.1 508 Loop detected",
			self::NotExtended => "HTTP/1.1 510 Not Extended",
			self::NetworkAuthenticationRequired => "HTTP/1.1 511 Network Authentication Required"
		};
	}


}