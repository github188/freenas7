#!/bin/sh
# Copyright © 2007 Volker Theile (votdev@gmx.de)
# All rights reserved.

# PROVIDE: rcconf
# REQUIRE: system_init
# BEFORE: CONFIG

. /etc/rc.subr
. /etc/util.subr
. /etc/configxml.subr

name="rcconf"

load_rc_config ${name}

echo -n "Updating rc.conf:"

sethostname()
{
	local _hostname

	_hostname=`/usr/local/bin/xml sel -t -v "concat(//system/hostname,'.',//system/domain)" ${configxml_file}`

	eval /usr/local/sbin/rconf attribute set hostname "${_hostname}"
}

setifconfig()
{
	local _ipaddress _if _interfaces _ifconf _subnet

	_ipaddress=`/usr/local/bin/xml sel -t -v "//interfaces/lan/ipaddr" ${configxml_file}`
	_if=`/usr/local/bin/xml sel -t -v "//interfaces/lan/if" ${configxml_file}`
	_if=`get_if ${_if}`

	case ${_ipaddress} in
		dhcp)
			_ifconf="DHCP"
			;;
		*)
			_ifconf=`/usr/local/bin/xml sel -t -o "${_ipaddress}/" -v "//interfaces/lan/subnet" ${configxml_file}`
			;;
	esac

	eval /usr/local/sbin/rconf attribute set "ifconfig_${_if}" "${_ifconf}"
}

# Update rcvar's. Use settings from config.xml.
for _rcscript in /etc/rc.d/*; do
	_rcscriptname=${_rcscript#/etc/rc.d/}
	if [ "${name}.sh" != "${_rcscriptname}" ]; then
		_xquery=`grep "XQUERY:" ${_rcscript} | sed 's/.*XQUERY: \(.*\)/\1/'`
		if [ -n "${_xquery}" ]; then
			_rcvar=`grep "RCVAR:" ${_rcscript} | sed 's/.*RCVAR: \(.*\)/\1/'`
			if [ -z "${_rcvar}" ]; then
				_rcvar=${_rcscriptname}
			fi

			# Execute query.
			_queryresult=`configxml_exec_query ${_xquery}`

			# Enable/disable service depending on query result.
			if [ "0" = "${_queryresult}" ]; then
				eval /usr/local/sbin/rconf service enable ${_rcvar}
				debug "rcconf.sh: ${_rcscriptname} service enabled"
			else
				eval /usr/local/sbin/rconf service disable ${_rcvar}
				debug "rcconf.sh: ${_rcscriptname} service disabled"
			fi

			echo -n "."
		fi
	fi
done

# Set hostname.
sethostname

# Set interface configuration.
setifconfig

# Finally do a line break.
echo

# Force reloading of rc.conf file.
_rc_conf_loaded=false
load_rc_config ${name}

return 0
