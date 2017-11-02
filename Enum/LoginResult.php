<?php

namespace bogdanaslt\SteamCommunity\Enum;

abstract class LoginResult
{
    const LoginOkay = "LoginOkay";
    const GeneralFailure = "GeneralFailure";
    const BadRSA = "BadRSA";
    const BadCredentials = "BadCredentials";
    const NeedCaptcha = "NeedCaptcha";
    const Need2FA = "Need2FA";
    const NeedEmail = "NeedEmail";
}
