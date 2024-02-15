<?php declare(strict_types=1);

namespace AO\Package;

enum Type: int {
	/** @psalm-return class-string */
	public function classIn(): string {
		return match ($this) {
			static::LOGIN_SEED => In\LoginSeed::class,
			static::LOGIN_OK => In\LoginOk::class,
			static::LOGIN_ERROR => In\LoginError::class,
			static::LOGIN_CHARLIST => In\LoginCharlist::class,
			static::CLIENT_UNKNOWN => In\ClientUnknown::class,
			static::CLIENT_NAME => In\ClientName::class,
			static::CLIENT_LOOKUP => In\ClientLookupResult::class,
			static::MSG_PRIVATE => In\Tell::class,
			static::MSG_VICINITY => In\VicinityMessage::class,
			static::MSG_VICINITYA => In\AnonVicinityMessage::class,
			static::MSG_SYSTEM => In\SystemMessage::class,
			static::CHAT_NOTICE => In\ChatNotice::class,
			static::BUDDY_ADD => In\BuddyAdded::class,
			static::BUDDY_REMOVE => In\BuddyRemoved::class,
			static::PRIVGRP_INVITE => In\PrivategroupInvited::class,
			static::PRIVGRP_KICK => In\PrivategroupKicked::class,
			static::PRIVGRP_PART => In\PrivategroupLeft::class,
			static::PRIVGRP_CLIJOIN => In\PrivategroupClientJoined::class,
			static::PRIVGRP_CLIPART => In\PrivategroupClientLeft::class,
			static::PRIVGRP_MESSAGE => In\PrivategroupMessage::class,
			static::PRIVGRP_REFUSE => In\PrivategroupRefuseInvite::class,
			static::GROUP_ANNOUNCE => In\GroupAnnounced::class,
			static::GROUP_PART => In\GroupLeft::class,
			static::GROUP_MESSAGE => In\GroupMessage::class,
			static::PING => In\Ping::class,
			static::ADM_MUX_INFO => In\AdmMuxInfo::class,
			default => throw new \InvalidArgumentException($this->name . " needs a class representation."),
		};
	}

	/** @psalm-return class-string */
	public function classOut(): string {
		return match ($this) {
			static::LOGIN_REQUEST => Out\LoginRequest::class,
			static::LOGIN_SELECT => Out\LoginSelectCharacter::class,
			static::CLIENT_LOOKUP => Out\NameLookup::class,
			static::MSG_PRIVATE => Out\Tell::class,
			static::BUDDY_ADD => Out\AddBuddy::class,
			static::BUDDY_REMOVE => Out\RemoveBuddy::class,
			static::ONLINE_SET => Out\SetOnlineStatus::class,
			static::PRIVGRP_INVITE => Out\InviteToPrivategroup::class,
			static::PRIVGRP_KICK => Out\KickFromPrivategroup::class,
			static::PRIVGRP_JOIN => Out\JoinPrivategroup::class,
			static::PRIVGRP_PART => Out\LeavePrivategroup::class,
			static::PRIVGRP_KICKALL => Out\KickAllFromPrivategroup::class,
			static::PRIVGRP_MESSAGE => Out\PrivategroupMessage::class,
			static::GROUP_DATA_SET => Out\GroupDataSet::class,
			static::GROUP_MESSAGE => Out\GroupMessage::class,
			static::GROUP_CM_SET => Out\GroupSetClientmode::class,
			static::CLIENTMODE_GET => Out\GetClientmode::class,
			static::CLIENTMODE_SET => Out\SetClientmode::class,
			static::PING => Out\Ping::class,
			static::CC => Out\Cc::class,

			default => throw new \InvalidArgumentException($this->name . " needs a class representation."),
		};
	}

	public function formatOut(): string {
		return match ($this) {
			static::LOGIN_SEED => "S",
			static::LOGIN_REQUEST => "ISS",
			static::LOGIN_SELECT => "I",
			static::CLIENT_LOOKUP => "S",
			static::MSG_PRIVATE => "ISS",
			static::BUDDY_ADD => "IS",
			static::BUDDY_REMOVE => "I",
			static::ONLINE_SET => "B",
			static::PRIVGRP_INVITE => "I",
			static::PRIVGRP_KICK => "I",
			static::PRIVGRP_JOIN => "I",
			static::PRIVGRP_PART => "I",
			static::PRIVGRP_KICKALL  => "",
			static::PRIVGRP_MESSAGE => "ISS",
			static::GROUP_DATA_SET => "GIS",
			static::GROUP_MESSAGE => "GSS",
			static::GROUP_CM_SET => "GIIII",
			static::CLIENTMODE_GET => "IG",
			static::CLIENTMODE_SET => "IIII",
			static::PING => "S",
			static::CC => "s",
			default => throw new \InvalidArgumentException($this->name . " has no defined send format"),
		};
	}

	public function formatIn(): string {
		return match ($this) {
			static::LOGIN_SEED => "S",
			static::LOGIN_OK => "",
			static::LOGIN_ERROR => "S",
			static::LOGIN_CHARLIST => "isib",
			static::CLIENT_UNKNOWN => "I",
			static::CLIENT_NAME => "IS",
			static::CLIENT_LOOKUP => "IS",
			static::MSG_PRIVATE => "ISS",
			static::MSG_VICINITY => "ISS",
			static::MSG_VICINITYA => "SSS",
			static::MSG_SYSTEM => "S",
			static::CHAT_NOTICE => "IIIS",
			static::BUDDY_ADD => "IBS",
			static::BUDDY_REMOVE => "I",
			static::PRIVGRP_INVITE => "I",
			static::PRIVGRP_KICK => "I",
			static::PRIVGRP_PART => "I",
			static::PRIVGRP_CLIJOIN => "II",
			static::PRIVGRP_CLIPART => "II",
			static::PRIVGRP_MESSAGE => "IISS",
			static::PRIVGRP_REFUSE => "II",
			static::GROUP_ANNOUNCE => "GSIS",
			static::GROUP_PART => "G",
			static::GROUP_MESSAGE => "GISS",
			static::PING => "S",
			static::ADM_MUX_INFO => "iii",
			default => throw new \InvalidArgumentException($this->name . " has no defined parse format"),
		};
	}

	case LOGIN_SEED = 0;
	case LOGIN_REQUEST = 2;
	case LOGIN_SELECT = 3;
	case LOGIN_OK = 5;
	case LOGIN_ERROR = 6;
	case LOGIN_CHARLIST = 7;
	case CLIENT_UNKNOWN = 10;
	case CLIENT_NAME = 20;
	case CLIENT_LOOKUP = 21;
	case MSG_PRIVATE = 30;
	case MSG_VICINITY = 34;
	case MSG_VICINITYA = 35;
	case MSG_SYSTEM = 36;
	case CHAT_NOTICE = 37;
	case BUDDY_ADD = 40;
	case BUDDY_REMOVE = 41;
	case ONLINE_SET = 42;
	case PRIVGRP_INVITE = 50;
	case PRIVGRP_KICK = 51;
	case PRIVGRP_JOIN = 52;
	case PRIVGRP_PART = 53;
	case PRIVGRP_KICKALL = 54;
	case PRIVGRP_CLIJOIN = 55;
	case PRIVGRP_CLIPART = 56;
	case PRIVGRP_MESSAGE = 57;
	case PRIVGRP_REFUSE = 58;
	case GROUP_ANNOUNCE = 60;
	case GROUP_PART = 61;
	case GROUP_DATA_SET = 64;
	case GROUP_MESSAGE = 65;
	case GROUP_CM_SET = 66;
	case CLIENTMODE_GET = 70;
	case CLIENTMODE_SET = 71;
	case PING = 100;
	case CC = 120;
	case ADM_MUX_INFO = 1100;
}
