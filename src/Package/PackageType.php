<?php declare(strict_types=1);

namespace AO\Package;

enum PackageType: int {
	/** @psalm-return class-string */
	public function classIn(): string {
		return match ($this) {
			static::LoginSeed => In\LoginSeed::class,
			static::LoginOK => In\LoginOk::class,
			static::LoginError => In\LoginError::class,
			static::LoginCharlist => In\LoginCharlist::class,
			static::CharacterUnknown => In\CharacterUnknown::class,
			static::CharacterName => In\CharacterName::class,
			static::CharacterLookup => In\CharacterLookupResult::class,
			static::PrivateMessage => In\Tell::class,
			static::VicinityMessage => In\VicinityMessage::class,
			static::BroadcastMessage => In\BroadcastMessage::class,
			static::SimpleSystemMessage => In\SimpleSystemMessage::class,
			static::SystemMessage => In\SimpleSystemMessage::class,
			static::BuddyAdd => In\BuddyState::class,
			static::BuddyRemove => In\BuddyRemoved::class,
			static::PrivateChannelInvite => In\PrivateChannelInvited::class,
			static::PrivateChannelKick => In\PrivateChannelKicked::class,
			static::PrivateChannelLeft => In\PrivateChannelLeft::class,
			static::PrivateChannelClientJoined => In\PrivateChannelClientJoined::class,
			static::PrivateChannelClientLeft => In\PrivateChannelClientLeft::class,
			static::PrivateChannelMessage => In\PrivateChannelMessage::class,
			static::PrivateChannelInviteRefused => In\PrivateChannelInviteRefused::class,
			static::PublicChannelJoined => In\GroupJoined::class,
			static::PublicChannelLeft => In\GroupLeft::class,
			static::PublicChannelMessage => In\GroupMessage::class,
			static::Ping => In\Ping::class,
			static::AdmMuxInfo => In\AdmMuxInfo::class,
			default => throw new \InvalidArgumentException($this->name . ' needs a class representation.'),
		};
	}

	/** @psalm-return class-string */
	public function classOut(): string {
		return match ($this) {
			static::LoginRequest => Out\LoginRequest::class,
			static::LoginSelect => Out\LoginSelectCharacter::class,
			static::CharacterLookup => Out\CharacterLookup::class,
			static::PrivateMessage => Out\Tell::class,
			static::BuddyAdd => Out\BuddyAdd::class,
			static::BuddyRemove => Out\BuddyRemove::class,
			static::SetOnlineStatus => Out\SetOnlineStatus::class,
			static::PrivateChannelInvite => Out\PrivateChannelInvite::class,
			static::PrivateChannelKick => Out\PrivateChannelKick::class,
			static::PrivateChannelJoin => Out\PrivateChannelJoin::class,
			static::PrivateChannelLeft => Out\PrivateChannelLeave::class,
			static::PrivateChannelKickAll => Out\PrivateChannelKickAll::class,
			static::PrivateChannelMessage => Out\PrivateChannelMessage::class,
			static::PublicChannelDataSet => Out\GroupDataSet::class,
			static::PublicChannelMessage => Out\GroupMessage::class,
			static::PublicChannelSetClientMode => Out\GroupSetClientMode::class,
			static::ClientModeGet => Out\ClientmodeGet::class,
			static::ClientModeSet => Out\ClientmodeSet::class,
			static::Ping => Out\Pong::class,
			static::ChatCommand => Out\ChatCommand::class,

			default => throw new \InvalidArgumentException($this->name . ' needs a class representation.'),
		};
	}

	case LoginSeed = 0;
	case LoginRequest = 2;
	case LoginSelect = 3;
	case LoginOK = 5;
	case LoginError = 6;
	case LoginCharlist = 7;
	case CharacterUnknown = 10;
	case CharacterName = 20;
	case CharacterLookup = 21;
	case PrivateMessage = 30;
	case VicinityMessage = 34;
	case BroadcastMessage = 35;
	case SimpleSystemMessage = 36;
	case SystemMessage = 37;
	case BuddyAdd = 40;
	case BuddyRemove = 41;
	case SetOnlineStatus = 42;
	case PrivateChannelInvite = 50;
	case PrivateChannelKick = 51;
	case PrivateChannelJoin = 52;
	case PrivateChannelLeft = 53;
	case PrivateChannelKickAll = 54;
	case PrivateChannelClientJoined = 55;
	case PrivateChannelClientLeft = 56;
	case PrivateChannelMessage = 57;
	case PrivateChannelInviteRefused = 58;
	case PublicChannelJoined = 60;
	case PublicChannelLeft = 61;
	case PublicChannelDataSet = 64;
	case PublicChannelMessage = 65;
	case PublicChannelSetClientMode = 66;
	case ClientModeGet = 70;
	case ClientModeSet = 71;
	case Ping = 100;
	case ChatCommand = 120;
	case AdmMuxInfo = 1_100;
}
