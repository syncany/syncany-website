select * from plugins
where
	    (pluginOperatingSystem='all' or pluginOperatingSystem=:pluginOperatingSystem)
	and (pluginArchitecture='all' or pluginArchitecture=:pluginArchitecture)
order by id desc